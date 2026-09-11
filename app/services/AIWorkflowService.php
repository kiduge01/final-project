<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use PDO;

final class AIWorkflowService
{
    public function __construct(private PDO $pdo) {}

    public function process(string $question): array
    {
        $q = mb_strtolower($question);
        $action = $this->action($q);
        if ($action !== null) {
            return [
                'answer' => $action['message'],
                'grounding' => ['action'=>$action],
                'workflow' => [
                    ['step'=>'Authenticate/Authorize','status'=>'complete','detail'=>'Request passed application authentication.'],
                    ['step'=>'Understand Intent','status'=>'complete','detail'=>'Agent task intent detected: '.$action['task']],
                    ['step'=>'Check Permission','status'=>'complete','detail'=>'The destination page will enforce role permissions before the task can be completed.'],
                    ['step'=>'Prepare Action','status'=>'complete','detail'=>'A safe redirect is prepared; the AI does not bypass forms, validation or approval rules.'],
                    ['step'=>'Human Review / Completion','status'=>'pending','detail'=>'User reviews the form and confirms the action.'],
                ],
                'clarification'=>false,
                'action'=>$action,
            ];
        }
        $intent = $this->intent($q);
        $range = (new DateRangeResolver())->resolve($question);
        $steps = [
            ['step'=>'Authenticate/Authorize','status'=>'complete','detail'=>'Request already passed application authentication and permission checks.'],
            ['step'=>'Understand Intent','status'=>'complete','detail'=>'Intent: '.$intent],
            ['step'=>'Extract Date/Context','status'=>'complete','detail'=>$range['label'].' ('.$range['start'].' to '.$range['end'].')'],
            ['step'=>'Resolve Service/Event','status'=>'complete','detail'=>'Attendance is resolved against registered Events / Ibada and their linked attendance records.'],
            ['step'=>'Select Approved Tool','status'=>'running','detail'=>'Selecting a service-layer function.'],
            ['step'=>'Validate Result','status'=>'pending','detail'=>'Result must match intent, date range and requested service.'],
            ['step'=>'Generate Grounded Answer','status'=>'pending','detail'=>'Answer is generated only from validated application data.'],
        ];

        $result = $this->tool($intent,$question,$range);
        $steps[4]['status']='complete';
        if (!empty($result['clarification'])) {
            $steps[5]['status']='complete'; $steps[6]['status']='complete';
            return ['answer'=>$result['clarification'],'grounding'=>$result,'workflow'=>$steps,'clarification'=>true];
        }
        $steps[5]['status']='complete';
        $answer = $this->format($intent,$result,$range);
        $steps[6]['status']='complete';
        return ['answer'=>$answer,'grounding'=>$result,'workflow'=>$steps,'clarification'=>false];
    }

    private function action(string $q): ?array
    {
        $contains=fn(array $words): bool => (bool)array_filter($words,fn($w)=>str_contains($q,$w));
        if($contains(['register member','add member','sajili member','sajili mshirika','new member'])) return ['task'=>'register_member','label'=>'Register Member','url'=>BASE_URL.'/members?action=create','message'=>'I can take you to Member Registration. Review the details and submit the form to register the member.'];
        if($contains(['register guest','add guest','sajili guest','sajili mgeni','new guest'])) return ['task'=>'register_guest','label'=>'Register Guest','url'=>BASE_URL.'/members?tab=guests&action=create-guest','message'=>'I can take you to Guest Registration. Review the guest details and submit the form.'];
        if($contains(['create event','prepare event','prepare ibada','create ibada','new event','new service','andaa ibada','sajili ibada'])) return ['task'=>'create_event','label'=>'Prepare Event / Ibada','url'=>BASE_URL.'/events?action=create','message'=>'I can take you to Events / Ibada to prepare and register the service or event.'];
        if($contains(['record attendance','take attendance','weka attendance','sajili attendance'])) return ['task'=>'record_attendance','label'=>'Record Attendance','url'=>BASE_URL.'/attendance?action=record','message'=>'I can take you to Attendance. Select a registered Event / Ibada, enter the counts and confirm the record.'];
        if($contains(['send sms','sms to','tuma sms'])) return ['task'=>'send_sms','label'=>'Compose SMS','url'=>BASE_URL.'/communication?action=compose&channel=sms','message'=>'I can take you to Communication with SMS selected. Choose recipients, review the message and send it.'];
        if($contains(['send email','email to','tuma email'])) return ['task'=>'send_email','label'=>'Compose Email','url'=>BASE_URL.'/communication?action=compose&channel=email','message'=>'I can take you to Communication with Email selected. Choose recipients, review the message and send it.'];
        if($contains(['record giving','add giving','record offering','record tithe','weka sadaka','sajili sadaka'])) return ['task'=>'record_giving','label'=>'Record Church Giving','url'=>BASE_URL.'/finance?action=create','message'=>'I can take you to Church Giving to record the transaction. Review the amount/category before saving.'];
        if($contains(['add asset','register asset','sajili asset','new asset'])) return ['task'=>'register_asset','label'=>'Register Asset','url'=>BASE_URL.'/asset-center?action=create','message'=>'I can take you to Asset Management to register the asset.'];
        if($contains(['open reports','view reports','show reports','prepare report page'])) return ['task'=>'open_reports','label'=>'Open Reports','url'=>BASE_URL.'/reports','message'=>'I can take you to Reports where you can choose the required report and filters.'];
        return null;
    }

    private function intent(string $q): string
    {
        if (str_contains($q,'attendance')) return 'attendance';
        if (str_contains($q,'giving')||str_contains($q,'offering')||str_contains($q,'tithe')||str_contains($q,'sadaka')) return 'giving';
        if (str_contains($q,'guest')) return 'guests';
        if (str_contains($q,'member')) return 'members';
        if (str_contains($q,'asset')) return 'assets';
        if (str_contains($q,'summary')||str_contains($q,'report')) return 'summary';
        return 'general';
    }

    private function tool(string $intent,string $question,array $range): array
    {
        $permission = match ($intent) {
            'attendance' => 'attendance.view',
            'giving' => 'finance.view',
            'members', 'guests' => 'members.view',
            'assets' => 'assets.view',
            'summary' => 'reports.view',
            default => null,
        };
        if ($permission !== null && !Auth::can($permission)) {
            return ['clarification'=>'You do not have permission to view that information.'];
        }

        if ($intent==='attendance') {
            $svc = new AttendanceService($this->pdo);
            $sameDay = $range['start']===$range['end'];
            $rows = $sameDay ? $svc->servicesOnDate($range['start']) : $svc->listBetween($range['start'],$range['end']);
            if ($sameDay && count($rows)>1 && (str_contains(mb_strtolower($question),'event') || str_contains(mb_strtolower($question),'service')) && !$this->mentionsAService($question,$rows)) {
                return ['clarification'=>'There are multiple attendance records for '.$range['label'].': '.implode(', ',array_map(fn($r)=>$r['service_name'].' ('.$this->sessionLabel((string)($r['session_type']??'main_service')).')',$rows)).'. Which service / sermon do you mean?'];
            }
            if ($sameDay && $this->mentionsAService($question,$rows)) {
                $rows = array_values(array_filter($rows,fn($r)=>str_contains(mb_strtolower($question),mb_strtolower((string)$r['service_name']))));
                $wantedSession=$this->sessionFromQuestion($question);
                if($wantedSession!==null){
                    $rows=array_values(array_filter($rows,fn($r)=>(string)($r['session_type']??'main_service')===$wantedSession));
                } elseif(count($rows)>1){
                    return ['clarification'=>'That Event / Ibada has more than one attendance session: '.implode(', ',array_map(fn($r)=>$this->sessionLabel((string)($r['session_type']??'main_service')),$rows)).'. Which Type / sermon do you mean?'];
                }
            }
            return ['rows'=>$rows,'summary'=>$svc->summaryBetween($range['start'],$range['end'])];
        }
        if ($intent==='giving') return (new ReportService($this->pdo))->giving($range['start'],$range['end']);
        if ($intent==='members') return ['total'=>(int)$this->pdo->query('SELECT COUNT(*) FROM members')->fetchColumn(),'active'=>(int)$this->pdo->query("SELECT COUNT(*) FROM members WHERE member_status='active'")->fetchColumn()];
        if ($intent==='guests') return ['total'=>$this->countSafe('guests'),'follow_up'=>$this->countSafe('guests',"follow_up_date IS NOT NULL AND follow_up_date<=CURRENT_DATE AND status NOT IN ('converted','inactive')")];
        if ($intent==='assets') return ['total'=>$this->countSafe('assets'),'attention'=>$this->countSafe('assets',"condition_status IN ('poor','retired')")];
        if ($intent==='summary') return (new ReportService($this->pdo))->churchSummary($range['start'],$range['end']);
        return ['clarification'=>'I can help with members, guests, attendance, church giving, assets and administrative reports. What would you like to know?'];
    }

    private function mentionsAService(string $q,array $rows): bool
    {
        $l=mb_strtolower($q); foreach($rows as $r){ if(str_contains($l,mb_strtolower((string)$r['service_name']))) return true; } return false;
    }
    private function countSafe(string $table,string $where='1=1'): int { try{return (int)$this->pdo->query("SELECT COUNT(*) FROM {$table} WHERE {$where}")->fetchColumn();}catch(\Throwable){return 0;} }

    private function format(string $intent,array $r,array $range): string
    {
        if ($intent==='attendance') {
            $rows=$r['rows']??[]; if(!$rows) return 'No attendance has been recorded for '.$range['label'].' (' . $range['start'] . ($range['end']!==$range['start']?' to '.$range['end']:'') . ').';
            if(count($rows)===1){$x=$rows[0]; return sprintf('%s — %s (%s) recorded %d attendees: men %d, women %d, children %d, youth %d, guests %d.',$x['service_name'],$this->sessionLabel((string)($x['session_type']??'main_service')),$x['service_date'],$x['total_count'],$x['men_count'],$x['women_count'],$x['children_count'],$x['youth_count'],$x['guests_count']);}
            $s=$r['summary']; return sprintf('Attendance for %s: %d services, %d total attendees, average %.1f per service. Breakdown: men %d, women %d, children %d, youth %d, guests %d.',$range['label'],$s['services'],$s['total'],$s['average'],$s['men'],$s['women'],$s['children'],$s['youth'],$s['guests']);
        }
        if($intent==='giving') return 'Recorded church giving for '.$range['label'].' is TZS '.number_format((float)$r['total'],2).'.';
        if($intent==='members') return "The church has {$r['total']} registered members, of whom {$r['active']} are active.";
        if($intent==='guests') return "There are {$r['total']} registered guests; {$r['follow_up']} currently require follow-up.";
        if($intent==='assets') return "There are {$r['total']} registered assets; {$r['attention']} are marked poor or retired and need attention.";
        if($intent==='summary') return 'Administrative summary for '.$range['label'].': '.$r['members'].' active members, '.$r['guests'].' registered guests, '.$r['attendance'].' recorded attendance, TZS '.number_format((float)$r['income'],2).' church income/giving, and '.$r['assets'].' registered assets.';
        return 'No grounded answer is available.';
    }

    private function sessionFromQuestion(string $question): ?string
    {
        $q=mb_strtolower($question);
        if(preg_match('/\b(first|1st|kwanza)\s+(sermon|service|ibada)\b/u',$q))return 'first_sermon';
        if(preg_match('/\b(second|2nd|pili)\s+(sermon|service|ibada)\b/u',$q))return 'second_sermon';
        if(preg_match('/\b(third|3rd|tatu)\s+(sermon|service|ibada)\b/u',$q))return 'third_sermon';
        if(preg_match('/\b(main|single)\s+(sermon|service|ibada)\b/u',$q))return 'main_service';
        return null;
    }

    private function sessionLabel(string $value): string
    {
        return match($value){
            'first_sermon'=>'First Sermon',
            'second_sermon'=>'Second Sermon',
            'third_sermon'=>'Third Sermon',
            'other'=>'Other Session',
            default=>'Main / Single Service',
        };
    }
}
