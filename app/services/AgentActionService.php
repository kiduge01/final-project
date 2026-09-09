<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Audit;
use App\Core\Auth;
use PDO;

final class AgentActionService
{
    public function __construct(private PDO $pdo) {}

    public function prepare(string $question, ?array $pending=null): ?array
    {
        $q=trim($question); $l=mb_strtolower($q);
        $task=$pending['task']??$this->detectTask($l);
        if(!$task) return null;
        $payload=$pending['payload']??[];
        $expected=$pending['expected']??null;
        $payload=$this->extract($task,$q,$payload,is_string($expected)?$expected:null);
        $missing=$this->missing($task,$payload);
        if($missing){
            $expected=$missing[0];
            return ['task'=>$task,'payload'=>$payload,'missing'=>$missing,'expected'=>$expected,'message'=>$this->missingMessage($task,$missing,$expected,$payload),'ready'=>false];
        }
        $summary=$this->summary($task,$payload);
        return ['task'=>$task,'payload'=>$payload,'missing'=>[],'message'=>$summary.' Please confirm before I perform this action.','ready'=>true,'summary'=>$summary];
    }

    public function execute(array $prepared): array
    {
        $task=(string)($prepared['task']??''); $p=(array)($prepared['payload']??[]); $user=Auth::user(); $uid=(int)($user['id']??0);
        return match($task){
            'register_asset'=>$this->asset($p,$uid),
            'create_event'=>$this->event($p,$uid),
            'register_member'=>$this->member($p,$uid),
            'register_guest'=>$this->guest($p,$uid),
            'send_sms'=>$this->message($p,$uid,'sms'),
            'send_email'=>$this->message($p,$uid,'email'),
            'record_attendance'=>$this->attendance($p,$uid),
            default=>['success'=>false,'message'=>'This agent action is not supported yet.'],
        };
    }

    private function detectTask(string $q): ?string
    {
        if(preg_match('/\b(add|register|sajili)\b.*\b(asset|assets|mic|mics|microphone|speaker|camera|projector)/u',$q)) return 'register_asset';
        if(preg_match('/\b(create|prepare|register|andaa|sajili)\b.*\b(event|ibada|service)/u',$q)) return 'create_event';
        if(preg_match('/\b(add|register|sajili)\b.*\bmember|mshirika/u',$q)) return 'register_member';
        if(preg_match('/\b(add|register|sajili)\b.*\bguest|mgeni/u',$q)) return 'register_guest';
        if(str_contains($q,'send sms')||str_contains($q,'tuma sms')) return 'send_sms';
        if(str_contains($q,'send email')||str_contains($q,'tuma email')) return 'send_email';
        if(preg_match('/\b(record|add|enter|weka|rekodi)\b.*\b(attendance|mahudhurio)\b/u',$q)||preg_match('/\b(attendance|mahudhurio)\b.*\b(record|add|enter|weka|rekodi)\b/u',$q)) return 'record_attendance';
        return null;
    }

    private function extract(string $task,string $q,array $p,?string $expected=null): array
    {
        $l=mb_strtolower($q);
        // Guided mode: when the assistant asked for one field, a plain reply fills that field.
        if($expected!==null && !preg_match('/[:=]/',$q)){
            $v=trim($q);
            if(in_array($expected,['category','location','venue','title','message','subject','audience','first_name','last_name','phone','gender'],true)) $p[$expected]=$v;
            if($expected==='session_type') $p['session_type']=$this->normalizeSessionType($v);
            if(in_array($expected,['men_count','women_count','children_count','youth_count','guests_count'],true) && preg_match('/\d+/',$v,$m)) $p[$expected]=(int)$m[0];
            if($expected==='date'){ try{$p['date']=(new \DateTimeImmutable($v))->format('Y-m-d');}catch(\Throwable){} }
            if($expected==='time' && preg_match('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/i',$v,$m)){ $h=(int)$m[1];$min=(int)($m[2]??0);$ap=strtolower($m[3]??'');if($ap==='pm'&&$h<12)$h+=12;if($ap==='am'&&$h===12)$h=0;$p['time']=sprintf('%02d:%02d',$h,$min);}
            if($expected==='event'){ $p['event_query']=$v; }
            if($expected==='items'){ $p=$this->extractAssetItems($v,$p); }
        }
        if($task==='register_asset'){
            if(empty($p['items'])) $p=$this->extractAssetItems($q,$p);
            if(preg_match('/category\s*[:=-]\s*([^,;]+)/i',$q,$m))$p['category']=trim($m[1]);
            elseif(str_contains($l,'sound equipment'))$p['category']='Sound Equipment';
            if(preg_match('/(?:location|venue)\s*[:=-]\s*([^,;]+)/i',$q,$m))$p['location']=trim($m[1]);
            elseif(str_contains($l,'main hall'))$p['location']='Main Hall';
        }
        if($task==='create_event'){
            if(preg_match('/(?:event|ibada|service)\s*(?:called|named|title|:)\s*([^,;]+)/i',$q,$m))$p['title']=trim($m[1]);
            elseif(str_contains($l,'sunday'))$p['title']=$p['title']??'Sunday Worship';
            if(preg_match('/(20\d{2}-\d{2}-\d{2})/',$q,$m))$p['date']=$m[1];
            elseif(preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](20\d{2})\b/',$q,$m))$p['date']=sprintf('%04d-%02d-%02d',$m[3],$m[2],$m[1]);
            if(preg_match('/\b(\d{1,2}:\d{2})\b/',$q,$m))$p['time']=$m[1];
            if(preg_match('/(?:venue|location)\s*[:=-]\s*([^,;]+)/i',$q,$m))$p['venue']=trim($m[1]); elseif(str_contains($l,'main hall'))$p['venue']='Main Hall';
            $p['event_type']=$p['event_type']??'service';
        }
        if(in_array($task,['send_sms','send_email'],true)){
            if(str_contains($l,'guest'))$p['audience']='guests'; elseif(str_contains($l,'member'))$p['audience']='all';
            if(preg_match('/(?:message|sms|email)\s*[:=-]\s*(.+)$/i',$q,$m))$p['message']=trim($m[1]);
            if($task==='send_email'&&preg_match('/subject\s*[:=-]\s*([^,;]+)/i',$q,$m))$p['subject']=trim($m[1]);
        }
        if(in_array($task,['register_member','register_guest'],true)){
            if(preg_match('/name\s*[:=-]\s*([^,;]+)/i',$q,$m)){$parts=preg_split('/\s+/',trim($m[1]),2);$p['first_name']=$parts[0]??'';$p['last_name']=$parts[1]??'';}
            if(preg_match('/phone\s*[:=-]\s*([+\d ]+)/i',$q,$m))$p['phone']=preg_replace('/\s+/','',trim($m[1]));
            if(preg_match('/email\s*[:=-]\s*([^,; ]+)/i',$q,$m))$p['email']=trim($m[1]);
            if($task==='register_member'&&preg_match('/gender\s*[:=-]\s*(male|female|other)/i',$q,$m))$p['gender']=strtolower($m[1]);
            if($task==='register_guest'&&preg_match('/location\s*[:=-]\s*([^,;]+)/i',$q,$m))$p['location']=trim($m[1]);
        }
        if($task==='record_attendance'){
            if(preg_match('/(?:event|ibada|service)\s*[:=-]\s*([^,;]+)/i',$q,$m))$p['event_query']=trim($m[1]);
            if(preg_match('/\b(first|1st|kwanza)\s+(?:sermon|service|ibada)\b/iu',$q))$p['session_type']='first_sermon';
            elseif(preg_match('/\b(second|2nd|pili)\s+(?:sermon|service|ibada)\b/iu',$q))$p['session_type']='second_sermon';
            elseif(preg_match('/\b(third|3rd|tatu)\s+(?:sermon|service|ibada)\b/iu',$q))$p['session_type']='third_sermon';
            elseif(preg_match('/\b(main|single)\s+(?:sermon|service|ibada)\b/iu',$q))$p['session_type']='main_service';
            foreach(['men'=>'men_count','women'=>'women_count','children'=>'children_count','youth'=>'youth_count','guests'=>'guests_count'] as $label=>$key) if(preg_match('/\b'.$label.'\s*[:=-]?\s*(\d+)/i',$q,$m))$p[$key]=(int)$m[1];
            if(!empty($p['event_query'])&&empty($p['event_id'])) $p=$this->resolveEvent($p);
        }
        return $p;
    }

    private function missing(string $task,array $p): array
    {
        return match($task){
            'register_asset'=>array_keys(array_filter(['items'=>empty($p['items']),'category'=>empty($p['category']),'location'=>empty($p['location'])])),
            'create_event'=>array_keys(array_filter(['title'=>empty($p['title']),'date'=>empty($p['date']),'time'=>empty($p['time'])])),
            'send_sms'=>array_keys(array_filter(['audience'=>empty($p['audience']),'message'=>empty($p['message'])])),
            'send_email'=>array_keys(array_filter(['audience'=>empty($p['audience']),'subject'=>empty($p['subject']),'message'=>empty($p['message'])])),
            'register_member'=>array_keys(array_filter(['first_name'=>empty($p['first_name']),'last_name'=>empty($p['last_name']),'phone'=>empty($p['phone']),'gender'=>empty($p['gender'])])),
            'register_guest'=>array_keys(array_filter(['first_name'=>empty($p['first_name']),'last_name'=>empty($p['last_name']),'phone'=>empty($p['phone']),'location'=>empty($p['location'])])),
            'record_attendance'=>array_keys(array_filter(['event'=>empty($p['event_id']),'session_type'=>empty($p['session_type']),'men_count'=>!array_key_exists('men_count',$p),'women_count'=>!array_key_exists('women_count',$p),'children_count'=>!array_key_exists('children_count',$p),'youth_count'=>!array_key_exists('youth_count',$p),'guests_count'=>!array_key_exists('guests_count',$p)])),
            default=>['details'],
        };
    }
    private function missingMessage(string $task,array $missing,string $expected,array $p): string
    {
        $labels=['items'=>'asset name, quantity and acquisition value','category'=>'category','location'=>'location','title'=>'event / Ibada name','date'=>'date','time'=>'start time','event'=>'registered Event / Ibada','session_type'=>'Type / sermon (Main/Single, First Sermon, Second Sermon, Third Sermon or Other)','men_count'=>'number of men','women_count'=>'number of women','children_count'=>'number of children','youth_count'=>'number of youth','guests_count'=>'number of guests','first_name'=>'first name','last_name'=>'last name','phone'=>'phone number','gender'=>'gender','audience'=>'recipient group','subject'=>'email subject','message'=>'message'];
        $all=implode(', ',array_map(fn($x)=>$labels[$x]??$x,$missing));$next=$labels[$expected]??$expected;
        return "I can complete this task here. Missing requirements: {$all}.\n\nWe can do it step by step, or you can send all remaining details in one message. First, please provide the {$next}.";
    }
    private function summary(string $task,array $p): string
    {
        if($task==='register_asset'){ $count=array_sum(array_map(fn($i)=>(int)$i['quantity'],$p['items']));$value=array_sum(array_map(fn($i)=>(float)$i['value']*(int)$i['quantity'],$p['items']));return "I am ready to register {$count} asset(s) in {$p['category']} at {$p['location']} with total acquisition value TZS ".number_format($value,0).'.'; }
        if($task==='create_event')return "I am ready to create {$p['title']} on {$p['date']} at {$p['time']}".(!empty($p['venue'])?' at '.$p['venue']:'').'.';
        if($task==='send_sms')return "I am ready to queue an SMS to {$p['audience']}: \"{$p['message']}\".";
        if($task==='send_email')return "I am ready to prepare an email to {$p['audience']} with subject \"{$p['subject']}\".";
        if($task==='record_attendance'){ $total=(int)$p['men_count']+(int)$p['women_count']+(int)$p['children_count']+(int)$p['youth_count']+(int)$p['guests_count'];$session=$this->sessionLabel((string)$p['session_type']);return "I am ready to record attendance for {$p['event_title']} ({$session}): men {$p['men_count']}, women {$p['women_count']}, children {$p['children_count']}, youth {$p['youth_count']}, guests {$p['guests_count']}. Total: {$total}."; }
        return 'I am ready to perform the requested registration.';
    }

    private function extractAssetItems(string $q,array $p): array
    {
        $items=[];
        if(preg_match_all('/(?:\b(\d+)\s*)?(microphones?|mics?|speakers?|cameras?|projectors?)(?:\s+(\d+))?\s*(?:@|at|x)?\s*(?:tzs\s*)?(\d[\d,]*)?/iu',$q,$m,PREG_SET_ORDER)){
            foreach($m as $x){$name=rtrim(strtolower($x[2]),'s');if($name==='mic')$name='microphone';$qty=(int)(($x[1]??'')!==''?$x[1]:(($x[3]??'')!==''?$x[3]:1));$price=isset($x[4])&&$x[4]!==''?(float)str_replace(',','',$x[4]):0;$items[]=['name'=>ucfirst($name),'quantity'=>max(1,$qty),'value'=>$price];}
        }
        if(!$items&&preg_match('/(?:asset\s*[:=-]?\s*)?([A-Za-z][A-Za-z0-9 -]{2,40})/i',$q,$m))$items[]=['name'=>trim($m[1]),'quantity'=>1,'value'=>0];
        if($items)$p['items']=$items;return $p;
    }
    private function resolveEvent(array $p): array
    {
        $q=trim((string)($p['event_query']??''));if($q==='')return $p;
        $st=$this->pdo->prepare("SELECT id,title,start_datetime FROM events WHERE status<>'cancelled' AND title LIKE :q ORDER BY ABS(DATEDIFF(DATE(start_datetime),CURRENT_DATE)),start_datetime DESC LIMIT 2");$st->execute([':q'=>'%'.$q.'%']);$rows=$st->fetchAll();
        if(count($rows)===1){$p['event_id']=(int)$rows[0]['id'];$p['event_title']=$rows[0]['title'].' — '.date('d M Y',strtotime($rows[0]['start_datetime']));}
        return $p;
    }
    private function attendance(array $p,int $uid): array
    {
        if(!Auth::can('attendance.record'))return ['success'=>false,'message'=>'You do not have permission to record attendance.'];
        $this->ensureAttendanceSessionColumn();
        $st=$this->pdo->prepare("SELECT id,title,event_type,start_datetime FROM events WHERE id=:id AND status<>'cancelled' LIMIT 1");$st->execute([':id'=>(int)$p['event_id']]);$event=$st->fetch();if(!$event)return ['success'=>false,'message'=>'The selected Event / Ibada no longer exists.'];
        $men=(int)$p['men_count'];$women=(int)$p['women_count'];$children=(int)$p['children_count'];$youth=(int)$p['youth_count'];$guests=(int)$p['guests_count'];$total=$men+$women+$children+$youth+$guests;if($total<=0)return ['success'=>false,'message'=>'Attendance total must be greater than zero.'];
        $date=date('Y-m-d',strtotime($event['start_datetime']));$map=['service'=>'sunday_service','seminar'=>'special','meeting'=>'other','appointment'=>'other','other'=>'other'];$type=$map[$event['event_type']]??'other';$sessionType=$this->normalizeSessionType((string)($p['session_type']??'main_service'));
        try{$sql="INSERT INTO attendance_snapshots (event_id,service_date,service_name,service_type,session_type,men_count,women_count,children_count,youth_count,guests_count,total_count,notes,created_by) VALUES (:eid,:d,:n,:t,:session,:m,:w,:c,:y,:g,:total,:notes,:uid)";$x=$this->pdo->prepare($sql);$x->execute([':eid'=>$event['id'],':d'=>$date,':n'=>$event['title'],':t'=>$type,':session'=>$sessionType,':m'=>$men,':w'=>$women,':c'=>$children,':y'=>$youth,':g'=>$guests,':total'=>$total,':notes'=>'Recorded through Church Assistant after confirmation',':uid'=>$uid]);$id=(int)$this->pdo->lastInsertId();Audit::log($this->pdo,$uid,'attendance','agent_create','attendance_snapshots',$id,null,['event_id'=>$event['id'],'session_type'=>$sessionType,'total'=>$total],'Church Assistant recorded attendance after confirmation');return ['success'=>true,'message'=>"Attendance for {$event['title']} (".$this->sessionLabel($sessionType).") recorded successfully. Total: {$total}.",'id'=>$id,'total'=>$total];}catch(\Throwable $e){return ['success'=>false,'message'=>'Could not record attendance. '.$e->getMessage()];}
    }



    private function ensureAttendanceSessionColumn(): void
    {
        try{
            $st=$this->pdo->query("SHOW COLUMNS FROM attendance_snapshots LIKE 'session_type'");
            if(!$st->fetch()){
                $this->pdo->exec("ALTER TABLE attendance_snapshots ADD COLUMN session_type VARCHAR(50) NOT NULL DEFAULT 'main_service' AFTER service_type, ADD INDEX idx_attendance_snapshots_session_type(session_type)");
            }
        }catch(\Throwable){
            // The normal migration remains the preferred installation path; execution will report a DB error if schema is unavailable.
        }
    }

    private function normalizeSessionType(string $value): string
    {
        $v=mb_strtolower(trim($value));
        if(preg_match('/(first|1st|kwanza)/u',$v))return 'first_sermon';
        if(preg_match('/(second|2nd|pili)/u',$v))return 'second_sermon';
        if(preg_match('/(third|3rd|tatu)/u',$v))return 'third_sermon';
        if(str_contains($v,'other'))return 'other';
        return 'main_service';
    }
    private function sessionLabel(string $value): string
    {
        return match($value){'first_sermon'=>'First Sermon','second_sermon'=>'Second Sermon','third_sermon'=>'Third Sermon','other'=>'Other Session',default=>'Main / Single Service'};
    }

    private function asset(array $p,int $uid): array
    {
        if(!Auth::can('assets.manage')&&!Auth::can('assets.create')) return ['success'=>false,'message'=>'You do not have permission to register assets.'];
        $st=$this->pdo->prepare('INSERT INTO assets (asset_tag,name,category,purchase_date,purchase_value,condition_status,current_location,is_active,notes,created_at,updated_at) VALUES (:tag,:name,:cat,CURRENT_DATE,:val,\'good\',:loc,1,:notes,NOW(),NOW())');$ids=[];
        $this->pdo->beginTransaction();try{foreach($p['items'] as $item){for($i=0;$i<(int)$item['quantity'];$i++){ $tag='AST-'.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6));$st->execute([':tag'=>$tag,':name'=>$item['name'].((int)$item['quantity']>1?' '.($i+1):''),':cat'=>$p['category'],':val'=>(float)$item['value'],':loc'=>$p['location'],':notes'=>'Registered by AI Agent after user confirmation']);$ids[]=(int)$this->pdo->lastInsertId();}}$this->pdo->commit();Audit::log($this->pdo,$uid,'assets','agent_create','assets',$ids[0]??null,null,['ids'=>$ids],'AI Agent registered assets after confirmation');return ['success'=>true,'message'=>count($ids).' asset(s) registered successfully.','ids'=>$ids];}catch(\Throwable $e){$this->pdo->rollBack();return ['success'=>false,'message'=>'Could not register the assets. '.$e->getMessage()];}
    }
    private function event(array $p,int $uid): array
    {
        if(!Auth::can('events.manage')&&!Auth::can('events.create')) return ['success'=>false,'message'=>'You do not have permission to create Events / Ibada.'];
        $code='EVT-'.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(2)),0,4));$start=$p['date'].' '.$p['time'].':00';$end=(new \DateTimeImmutable($start))->modify('+2 hours')->format('Y-m-d H:i:s');
        $st=$this->pdo->prepare('INSERT INTO events (event_code,title,event_type,start_datetime,end_datetime,venue,location,organizer_user_id,status,description,created_at,updated_at) VALUES (:c,:t,:type,:s,:e,:v,:v,:u,\'planned\',:d,NOW(),NOW())');$st->execute([':c'=>$code,':t'=>$p['title'],':type'=>$p['event_type']??'service',':s'=>$start,':e'=>$end,':v'=>$p['venue']??null,':u'=>$uid,':d'=>'Prepared by AI Agent after user confirmation']);$id=(int)$this->pdo->lastInsertId();Audit::log($this->pdo,$uid,'events','agent_create','events',$id,null,$p,'AI Agent created event after confirmation');return ['success'=>true,'message'=>'Event / Ibada created successfully.','id'=>$id];
    }
    private function member(array $p,int $uid): array
    {
        if(!Auth::can('members.manage')&&!Auth::can('members.create')) return ['success'=>false,'message'=>'You do not have permission to register members.'];
        $code='MEM-'.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(2)),0,4));$st=$this->pdo->prepare('INSERT INTO members (member_code,first_name,last_name,gender,phone,email,join_date,member_status,created_by,created_at,updated_at) VALUES (:c,:f,:l,:g,:p,:e,CURRENT_DATE,\'active\',:u,NOW(),NOW())');$st->execute([':c'=>$code,':f'=>$p['first_name'],':l'=>$p['last_name'],':g'=>$p['gender'],':p'=>$p['phone'],':e'=>$p['email']??null,':u'=>$uid]);$id=(int)$this->pdo->lastInsertId();return ['success'=>true,'message'=>'Member registered successfully.','id'=>$id];
    }
    private function guest(array $p,int $uid): array
    {
        if(!Auth::can('members.manage')&&!Auth::can('members.create')) return ['success'=>false,'message'=>'You do not have permission to register guests.'];
        $code='GST-'.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(2)),0,4));$st=$this->pdo->prepare('INSERT INTO guests (guest_code,first_name,last_name,phone,email,location,service_date,visit_type,status,created_by,created_at,updated_at) VALUES (:c,:f,:l,:p,:e,:loc,CURRENT_DATE,\'first_time\',\'registered\',:u,NOW(),NOW())');$st->execute([':c'=>$code,':f'=>$p['first_name'],':l'=>$p['last_name'],':p'=>$p['phone'],':e'=>$p['email']??null,':loc'=>$p['location'],':u'=>$uid]);$id=(int)$this->pdo->lastInsertId();return ['success'=>true,'message'=>'Guest registered successfully.','id'=>$id];
    }
    private function message(array $p,int $uid,string $channel): array
    {
        if(!Auth::can('communication.send')) return ['success'=>false,'message'=>'You do not have permission to send communication.'];
        $aud=$p['audience']==='guests'?'guests':'all';$subject=$channel==='email'?($p['subject']??'Church communication'):null;
        $sql=$aud==='guests'?'SELECT id,phone,email FROM guests ORDER BY id':'SELECT id,phone,email FROM members WHERE member_status=\'active\' ORDER BY id';
        try{$contacts=$this->pdo->query($sql)->fetchAll();}catch(\Throwable $e){return ['success'=>false,'message'=>'Unable to load message recipients.'];}
        if(!$contacts)return ['success'=>false,'message'=>'No recipients were found for this audience.'];
        $sent=0;$failed=0;$messageId=null;
        try{
            $this->pdo->beginTransaction();
            $st=$this->pdo->prepare('INSERT INTO messages (subject,message_text,recipient_type,recipient_ids,recipient_count,sent_count,failed_count,channel,status,sent_by,created_at,updated_at) VALUES (:sub,:msg,:aud,NULL,:cnt,0,0,:ch,\'sending\',:uid,NOW(),NOW())');
            $st->execute([':sub'=>$subject,':msg'=>$p['message'],':aud'=>$aud,':cnt'=>count($contacts),':ch'=>$channel,':uid'=>$uid]);$messageId=(int)$this->pdo->lastInsertId();
            foreach($contacts as $c){
                try{
                    if($channel==='sms'){
                        $phone=trim((string)($c['phone']??'')); if($phone===''){ $failed++; continue; }
                        $res=$this->dispatchSms($phone,(string)$p['message']);
                        $log=$this->pdo->prepare('INSERT INTO sms_logs (message_id,recipient_type,member_id,phone,message_text,message_type,provider,provider_message_id,delivery_status,sent_by,sent_at,created_at) VALUES (:mid,:rt,:member,:phone,:msg,\'other\',:provider,:pid,:status,:uid,NOW(),NOW())');
                        $log->execute([':mid'=>$messageId,':rt'=>$aud==='guests'?'guest':'member',':member'=>$aud==='guests'?null:(int)$c['id'],':phone'=>$phone,':msg'=>$p['message'],':provider'=>$res['provider'],':pid'=>$res['provider_message_id'],':status'=>$res['status'],':uid'=>$uid]);$sent++;
                    }else{
                        $email=trim((string)($c['email']??''));if(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $failed++;continue; }
                        if($this->dispatchEmail($email,(string)$subject,(string)$p['message']))$sent++;else$failed++;
                    }
                }catch(\Throwable){$failed++;}
            }
            $status=$failed===0?'sent':($sent===0?'failed':'partial');
            $this->pdo->prepare('UPDATE messages SET sent_count=:s,failed_count=:f,status=:st,updated_at=NOW() WHERE id=:id')->execute([':s'=>$sent,':f'=>$failed,':st'=>$status,':id'=>$messageId]);
            $this->pdo->commit();Audit::log($this->pdo,$uid,'communication','agent_send','messages',$messageId,null,['channel'=>$channel,'audience'=>$aud,'sent'=>$sent,'failed'=>$failed],'AI Agent sent communication after confirmation');
            return ['success'=>$sent>0,'message'=>strtoupper($channel).' processed for '.count($contacts).' recipient(s): '.$sent.' sent, '.$failed.' failed.','id'=>$messageId,'sent'=>$sent,'failed'=>$failed];
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();return ['success'=>false,'message'=>'Could not send the message. '.$e->getMessage()];}
    }

    private function dispatchSms(string $phone,string $message): array
    {
        $apiKey=trim((string)getenv('SMS_API_KEY'));$appId=trim((string)getenv('SMS_APP_ID'));$senderId=trim((string)getenv('SMS_SENDER_ID'));$endpoint=trim((string)(getenv('SMS_API_ENDPOINT')?:'https://karibu.briq.tz/v1/message/send-instant'));
        if($apiKey===''||$senderId==='')throw new \RuntimeException('SMS provider credentials are not configured');if(!function_exists('curl_init'))throw new \RuntimeException('PHP cURL extension is required');
        $headers=['Content-Type: application/json','X-API-Key: '.$apiKey];if($appId!=='')$headers[]='X-App-ID: '.$appId;$ch=curl_init($endpoint);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>$headers,CURLOPT_POSTFIELDS=>json_encode(['content'=>$message,'recipients'=>[$phone],'sender_id'=>$senderId])]);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($raw===false||$code<200||$code>=300)throw new \RuntimeException('SMS provider rejected the request');$j=json_decode((string)$raw,true)?:[];return ['provider'=>'briq','status'=>'sent','provider_message_id'=>$j['message_id']??$j['data']['message_id']??$j['id']??null];
    }
    private function dispatchEmail(string $email,string $subject,string $message): bool
    {
        $from=trim((string)getenv('MAIL_FROM_ADDRESS'));if($from===''||!filter_var($from,FILTER_VALIDATE_EMAIL))throw new \RuntimeException('MAIL_FROM_ADDRESS is not configured');if(!function_exists('mail'))throw new \RuntimeException('PHP mail() is unavailable');$name=trim((string)(getenv('MAIL_FROM_NAME')?:'Church Communication'));$headers=['MIME-Version: 1.0','Content-Type: text/plain; charset=UTF-8','From: '.mb_encode_mimeheader($name,'UTF-8').' <'.$from.'>','Reply-To: '.$from];return mail($email,mb_encode_mimeheader($subject,'UTF-8'),$message,implode("\r\n",$headers));
    }
}
