<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Response;
use App\Services\AIWorkflowService;
use App\Services\AgentActionService;
use App\Services\DateRangeResolver;
use App\Services\ReportComparisonService;
use PDO;

final class AIController
{
    public function __construct(private PDO $pdo) {}

    public function query(array $input): void
    {
        if (!Auth::check()) Response::json(['success'=>false,'message'=>'Unauthenticated'],401);
        $question=trim((string)($input['question']??''));
        if($question==='') Response::json(['success'=>false,'message'=>'Question is required'],422);
        if(mb_strlen($question)>500) Response::json(['success'=>false,'message'=>'Question is too long'],422);

        $agent=new AgentActionService($this->pdo);
        if (in_array(mb_strtolower($question), ['cancel','cancel task','stop','acha'], true)) { unset($_SESSION['ai_pending_action']); Response::json(['success'=>true,'data'=>['answer'=>'Pending agent task cancelled.','mode'=>'agent-action','clarification'=>false,'action'=>null]]); }
        if($this->isReportRequest($question)) $this->report($question);

        $pending=$_SESSION['ai_pending_action']??null;
        $prepared=$agent->prepare($question,is_array($pending)?$pending:null);
        if($prepared!==null){
            if($prepared['ready']){
                $token=bin2hex(random_bytes(16));
                $_SESSION['ai_pending_action']=['task'=>$prepared['task'],'payload'=>$prepared['payload'],'token'=>$token,'expires'=>time()+600];
                $answer=$prepared['message'];
                $this->log($question,$answer,['agent_task'=>$prepared['task'],'ready'=>true]);
                Response::json(['success'=>true,'data'=>[
                    'answer'=>$answer,'mode'=>'agent-action','clarification'=>false,
                    'action'=>['type'=>'confirm','token'=>$token,'label'=>'Confirm action','task'=>$prepared['task'],'summary'=>$prepared['summary']??$answer]
                ]]);
            }
            $_SESSION['ai_pending_action']=['task'=>$prepared['task'],'payload'=>$prepared['payload'],'expected'=>$prepared['expected']??null,'expires'=>time()+600];
            $answer=$prepared['message'];
            $this->log($question,$answer,['agent_task'=>$prepared['task'],'missing'=>$prepared['missing']]);
            Response::json(['success'=>true,'data'=>['answer'=>$answer,'mode'=>'agent-action','clarification'=>true,'action'=>null]]);
        }

        // Clear stale pending action when the conversation moves back to a normal data question.
        if(isset($_SESSION['ai_pending_action']) && (($_SESSION['ai_pending_action']['expires']??0)<time())) unset($_SESSION['ai_pending_action']);

        $workflow=(new AIWorkflowService($this->pdo))->process($question);
        $answer=$workflow['answer'];
        if(empty($workflow['clarification'])&&empty($workflow['action'])){
            $polished=$this->generateWithOpenAI($question,$answer);
            if($polished!==null)$answer=$polished;
        }
        $this->log($question,$answer,['clarification'=>(bool)$workflow['clarification'],'workflow'=>$workflow['workflow']]);
        Response::json(['success'=>true,'data'=>['answer'=>$answer,'mode'=>'service-layer-grounded-assistant','workflow'=>$workflow['workflow'],'clarification'=>(bool)$workflow['clarification'],'action'=>null]]);
    }

    public function confirm(array $input): void
    {
        if(!Auth::check()) Response::json(['success'=>false,'message'=>'Unauthenticated'],401);
        $token=trim((string)($input['token']??''));$pending=$_SESSION['ai_pending_action']??null;
        if(!is_array($pending)||empty($pending['token'])||!hash_equals((string)$pending['token'],$token)||($pending['expires']??0)<time()){
            unset($_SESSION['ai_pending_action']);Response::json(['success'=>false,'message'=>'This agent confirmation has expired. Please ask the assistant to prepare the task again.'],422);
        }
        $result=(new AgentActionService($this->pdo))->execute($pending);
        unset($_SESSION['ai_pending_action']);
        $this->log('CONFIRM '.$pending['task'],(string)$result['message'],['agent_task'=>$pending['task'],'executed'=>(bool)($result['success']??false)]);
        Response::json(['success'=>(bool)($result['success']??false),'message'=>$result['message']??'Action completed','data'=>$result],($result['success']??false)?200:422);
    }

    public function cancel(): void
    {
        unset($_SESSION['ai_pending_action']);
        Response::json(['success'=>true,'message'=>'Pending agent task cancelled.']);
    }

    public function summary(): void { $this->query(['question'=>'Prepare an administrative summary for this month.']); }

    private function isReportRequest(string $question): bool
    {
        $q=mb_strtolower($question);
        return str_contains($q,'report') && (bool)preg_match('/\b(generate|create|prepare|make|download|pdf|toa|andaa|tengeneza)\b/u',$q);
    }

    private function report(string $question): void
    {
        if(!Auth::can('reports.view')){
            Response::json(['success'=>true,'data'=>[
                'answer'=>'You do not have permission to generate reports.',
                'mode'=>'report-generator',
                'clarification'=>true,
                'action'=>null
            ]]);
        }
        $range=(new DateRangeResolver())->resolve($question);
        $data=(new ReportComparisonService($this->pdo))->build($range['start'],$range['end']);
        $type='Overview';
        $downloadUrl=BASE_URL.'/api/v1/reports/download/pdf?'.http_build_query(['start'=>$range['start'],'end'=>$range['end'],'type'=>$type]);
        $answer='I generated an '.$type.' report for '.$range['label'].' ('.$range['start'].' to '.$range['end'].') using the Reports Center format.';
        $this->log($question,$answer,['report_range'=>$range,'report_type'=>$type]);
        Response::json(['success'=>true,'data'=>[
            'answer'=>$answer,
            'mode'=>'report-generator',
            'workflow'=>[
                ['step'=>'Resolve Report Period','status'=>'complete','detail'=>$range['label'].' ('.$range['start'].' to '.$range['end'].')'],
                ['step'=>'Build Report Data','status'=>'complete','detail'=>'Used the same comparison service as the Reports Center.'],
                ['step'=>'Prepare PDF Link','status'=>'complete','detail'=>'Download link points to the Reports Center PDF endpoint.'],
            ],
            'clarification'=>false,
            'action'=>['type'=>'report','label'=>'Download PDF','download_url'=>$downloadUrl,'report_type'=>$type,'report'=>$data]
        ]]);
    }

    private function log(string $question,string $answer,array $meta=[]): void
    {
        $this->ensureLogTable();$user=Auth::user();$actorId=isset($user['id'])?(int)$user['id']:null;
        $st=$this->pdo->prepare('INSERT INTO ai_logs (user_id,question,answer,created_at) VALUES (:uid,:q,:a,NOW())');$st->execute([':uid'=>$actorId,':q'=>$question,':a'=>$answer]);$id=(int)$this->pdo->lastInsertId();
        if($actorId)Audit::log($this->pdo,$actorId,'ai','query','ai_logs',$id,null,array_merge(['question'=>$question],$meta),'AI assistant interaction');
    }

    private function generateWithOpenAI(string $question,string $groundedAnswer): ?string
    {
        $apiKey=trim((string)getenv('OPENAI_API_KEY'));if($apiKey===''||!function_exists('curl_init'))return null;
        $model=trim((string)(getenv('OPENAI_MODEL')?:'gpt-5.6'));
        $endpoint=trim((string)(getenv('OPENAI_RESPONSES_ENDPOINT')?:'https://api.openai.com/v1/responses'));
        $payload=['model'=>$model,'input'=>[['role'=>'system','content'=>'You are a church administration assistant. Rewrite the validated application answer clearly and concisely. Preserve every date and number exactly. Do not add facts, SQL, external data, or assumptions.'],['role'=>'user','content'=>"Question: {$question}\nValidated application answer: {$groundedAnswer}"]]];
        $ch=curl_init($endpoint);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$apiKey],CURLOPT_POSTFIELDS=>json_encode($payload)]);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($raw===false||$code<200||$code>=300)return null;$data=json_decode((string)$raw,true);if(!is_array($data))return null;if(!empty($data['output_text']))return trim((string)$data['output_text']);foreach(($data['output']??[]) as $item)foreach(($item['content']??[]) as $content)if(!empty($content['text']))return trim((string)$content['text']);return null;
    }
    private function ensureLogTable(): void{$this->pdo->exec('CREATE TABLE IF NOT EXISTS ai_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NULL,question TEXT NOT NULL,answer LONGTEXT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_ai_logs_user(user_id),INDEX idx_ai_logs_created(created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');}
}
