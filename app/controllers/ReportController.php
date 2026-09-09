<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Services\ReportComparisonService;
use App\Services\SimplePdf;
use PDO;

final class ReportController
{
    public function __construct(private PDO $pdo) {}

    public function comparison(): void
    {
        if(!Auth::can('reports.view')) Response::json(['success'=>false,'message'=>'Forbidden'],403);
        [$s,$e]=$this->range();
        $data=(new ReportComparisonService($this->pdo))->build($s,$e);
        $data['ai_trend_summary']=$this->polishTrend($data['trend_summary'],$data);
        Response::json(['success'=>true,'data'=>$data]);
    }

    public function downloadPdf(): void
    {
        if(!Auth::can('reports.view')) Response::json(['success'=>false,'message'=>'Forbidden'],403);
        [$s,$e]=$this->range();$description=trim((string)($_GET['description']??''));$type=trim((string)($_GET['type']??'Overview'));
        $data=(new ReportComparisonService($this->pdo))->build($s,$e);$data['ai_trend_summary']=$this->polishTrend($data['trend_summary'],$data);$c=$data['current'];$p=$data['previous'];
        $brand=\App\Core\Response::loadChurchBranding();$user=Auth::user();
        $pdf=new SimplePdf();
        $pdf->line(strtoupper((string)$brand['church_name']),16,true);$pdf->line(strtoupper($type).' REPORT',14,true);$pdf->line("Reporting period: {$s} to {$e}",10);$pdf->line('Prepared by: '.($user['full_name']??'Administrator'),10);$pdf->line('Generated: '.date('Y-m-d H:i'),10);$pdf->gap();
        if($description!==''){$pdf->line('REPORT DESCRIPTION',11,true);$pdf->line($description,10);$pdf->gap();}
        $pdf->line('EXECUTIVE SUMMARY',11,true);$pdf->line($data['ai_trend_summary']??$data['trend_summary'],10);$pdf->gap();
        $pdf->line('KEY STATISTICS',11,true);
        $pdf->line('Active members: '.number_format((int)$c['members_active']).' | New members: '.number_format((int)$c['new_members']));
        $pdf->line('Guest visits in period: '.number_format((int)$c['guests_period']).' | Total registered guests: '.number_format((int)$c['guests_total']));
        $pdf->line('Attendance: '.number_format((int)$c['attendance']).' across '.number_format((int)$c['attendance_services']).' Ibada/events | Average: '.number_format((float)$c['attendance_average'],1));
        $pdf->line('Church giving: TZS '.number_format((float)$c['giving'],2).' | Expenses: TZS '.number_format((float)$c['expenses'],2));
        $pdf->line('Registered assets: '.number_format((int)$c['assets']));$pdf->gap();
        $pdf->line('COMPARISON WITH PREVIOUS EQUAL PERIOD',11,true);$pdf->line('Previous period: '.$data['comparison_range']['start'].' to '.$data['comparison_range']['end']);
        foreach(['attendance'=>'Attendance','attendance_average'=>'Average attendance','guests_period'=>'Guest visits','giving'=>'Church giving','new_members'=>'New members'] as $k=>$label){$x=$data['changes'][$k];$pct=$x['percent'];$pdf->line($label.': '.number_format((float)$x['current'],1).' vs '.number_format((float)$x['previous'],1).' | change '.($x['difference']>=0?'+':'').number_format((float)$x['difference'],1).($pct===null?'':' ('.($pct>=0?'+':'').$pct.'%)'));}
        $pdf->gap();$pdf->line('ATTENDANCE / IBADA DETAILS',11,true);$pdf->line('Date | Ibada/Event | Men | Women | Children | Youth | Guests | Total',9,true);
        foreach(array_slice($c['attendance_rows'],0,30) as $r){$sessionLabels=['main_service'=>'Main / Single Service','first_sermon'=>'First Sermon','second_sermon'=>'Second Sermon','third_sermon'=>'Third Sermon','other'=>'Other Session'];$session=$sessionLabels[$r['session_type']??'main_service']??($r['session_type']??'Main / Single Service');$pdf->line($r['service_date'].' | '.$r['service_name'].' | '.$session.' | '.$r['men_count'].' | '.$r['women_count'].' | '.$r['children_count'].' | '.$r['youth_count'].' | '.$r['guests_count'].' | '.$r['total_count'],8);}
        $pdf->gap();$pdf->line('GIVING BREAKDOWN',11,true);foreach($c['giving_breakdown'] as $r)$pdf->line(($r['category']??'Giving').': TZS '.number_format((float)($r['amount']??0),2),9);
        $pdf->gap();$pdf->line('AI-ASSISTED TREND INTERPRETATION',11,true);$pdf->line($data['ai_trend_summary']??$data['trend_summary'],10);
        $pdf->output('TCRIC_'.$type.'_'.$s.'_to_'.$e.'.pdf');
    }


    private function polishTrend(string $grounded, array $data): string
    {
        $apiKey=trim((string)getenv('OPENAI_API_KEY'));
        if($apiKey===''||!function_exists('curl_init')) return $grounded;
        $payload=[
            'model'=>trim((string)(getenv('OPENAI_MODEL')?:'gpt-5.6')),
            'input'=>[
                ['role'=>'system','content'=>'You are an administrative reporting assistant. Explain trends using only the supplied calculated current and previous-period figures. Mention meaningful increases, decreases, stability and the strongest attendance record. Preserve all figures exactly. Do not invent causes unless the data explicitly contains them. Keep the analysis concise and professional.'],
                ['role'=>'user','content'=>'Validated comparison data: '.json_encode(['range'=>$data['range'],'comparison_range'=>$data['comparison_range'],'changes'=>$data['changes'],'highest_attendance'=>$data['current']['highest_attendance']??null,'grounded_summary'=>$grounded],JSON_UNESCAPED_UNICODE)]
            ]
        ];
        $endpoint=trim((string)(getenv('OPENAI_RESPONSES_ENDPOINT')?:'https://api.openai.com/v1/responses'));
        $ch=curl_init($endpoint);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>6,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$apiKey],CURLOPT_POSTFIELDS=>json_encode($payload)]);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
        if($raw===false||$code<200||$code>=300)return $grounded;$j=json_decode((string)$raw,true);if(!is_array($j))return $grounded;if(!empty($j['output_text']))return trim((string)$j['output_text']);foreach(($j['output']??[]) as $item)foreach(($item['content']??[]) as $content)if(!empty($content['text']))return trim((string)$content['text']);return $grounded;
    }

    private function range(): array
    {
        $s=trim((string)($_GET['start']??$_GET['date_from']??date('Y-m-01')));$e=trim((string)($_GET['end']??$_GET['date_to']??date('Y-m-t')));
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$s)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$e)||$s>$e) Response::json(['success'=>false,'message'=>'Invalid reporting period'],422);
        return [$s,$e];
    }
}
