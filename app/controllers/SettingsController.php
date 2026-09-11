<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Response;
use PDO;

final class SettingsController
{
    public function __construct(private PDO $pdo) {}

    public function account(): void
    {
        $u=Auth::user(); $id=(int)($u['id']??0);
        $st=$this->pdo->prepare('SELECT u.id,u.full_name,u.email,u.phone,u.is_active,u.last_login_at,u.created_at,r.name role_name FROM users u LEFT JOIN roles r ON r.id=u.role_id WHERE u.id=:id LIMIT 1');
        $st->execute([':id'=>$id]);
        Response::json(['success'=>true,'data'=>$st->fetch()?:[]]);
    }

    public function updateAccount(array $input): void
    {
        $u=Auth::user(); $id=(int)($u['id']??0);
        $name=trim((string)($input['full_name']??'')); $email=trim((string)($input['email']??'')); $phone=trim((string)($input['phone']??''));
        if($name===''||$phone==='') Response::json(['success'=>false,'message'=>'Full name and phone are required'],422);
        if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL)) Response::json(['success'=>false,'message'=>'Enter a valid email address'],422);
        $chk=$this->pdo->prepare('SELECT id FROM users WHERE (phone=:p OR (:e<>\'\' AND email=:e)) AND id<>:id LIMIT 1');$chk->execute([':p'=>$phone,':e'=>$email,':id'=>$id]);
        if($chk->fetch()) Response::json(['success'=>false,'message'=>'Phone or email is already used by another account'],409);
        $this->pdo->prepare('UPDATE users SET full_name=:n,email=:e,phone=:p,updated_at=NOW() WHERE id=:id')->execute([':n'=>$name,':e'=>$email,':p'=>$phone,':id'=>$id]);
        $_SESSION['user']['full_name']=$name;
        Audit::log($this->pdo,$id,'settings','update_account','users',$id,null,['full_name'=>$name,'email'=>$email,'phone'=>$phone],'Updated own account profile');
        Response::json(['success'=>true,'message'=>'Account updated']);
    }

    public function changePassword(array $input): void
    {
        $u=Auth::user(); $id=(int)($u['id']??0);$current=(string)($input['current_password']??'');$new=(string)($input['new_password']??'');$confirm=(string)($input['confirm_password']??'');
        if(strlen($new)<8) Response::json(['success'=>false,'message'=>'New password must be at least 8 characters'],422);
        if($new!==$confirm) Response::json(['success'=>false,'message'=>'New passwords do not match'],422);
        $st=$this->pdo->prepare('SELECT password_hash FROM users WHERE id=:id');$st->execute([':id'=>$id]);$hash=(string)$st->fetchColumn();
        if(!$hash||!password_verify($current,$hash)) Response::json(['success'=>false,'message'=>'Current password is incorrect'],422);
        $this->pdo->prepare('UPDATE users SET password_hash=:h,updated_at=NOW() WHERE id=:id')->execute([':h'=>password_hash($new,PASSWORD_DEFAULT),':id'=>$id]);
        Audit::log($this->pdo,$id,'settings','change_password','users',$id,null,null,'Changed account password');
        Response::json(['success'=>true,'message'=>'Password changed successfully']);
    }

    public function preferences(): void
    {
        $keys=['church_name','church_address','church_phone','church_email','timezone','report_default_period','sms_sender_id','sms_provider','email_from_name','email_from_address','session_timeout_minutes','notifications_enabled'];
        $placeholders=implode(',',array_fill(0,count($keys),'?'));
        try{$st=$this->pdo->prepare("SELECT setting_key,setting_value FROM church_settings WHERE setting_key IN ($placeholders)");$st->execute($keys);$data=[];foreach($st->fetchAll() as $r)$data[$r['setting_key']]=$r['setting_value'];Response::json(['success'=>true,'data'=>$data]);}catch(\Throwable){Response::json(['success'=>true,'data'=>[]]);}
    }

    public function updatePreferences(array $input): void
    {
        if(!Auth::can('settings.manage')) Response::json(['success'=>false,'message'=>'Forbidden'],403);
        $allowed=['church_name','church_address','church_phone','church_email','timezone','report_default_period','sms_sender_id','sms_provider','email_from_name','email_from_address','session_timeout_minutes','notifications_enabled'];
        $st=$this->pdo->prepare('INSERT INTO church_settings(setting_key,setting_value) VALUES(:k,:v) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()');
        foreach($allowed as $k) if(array_key_exists($k,$input))$st->execute([':k'=>$k,':v'=>is_bool($input[$k])?($input[$k]?'1':'0'):(string)$input[$k]]);
        $uid=(int)(Auth::user()['id']??0);Audit::log($this->pdo,$uid,'settings','update_preferences','church_settings',null,null,array_intersect_key($input,array_flip($allowed)),'Updated system settings');
        Response::json(['success'=>true,'message'=>'System settings saved']);
    }

    public function auditLogs(): void
    {
        if(!Auth::can('settings.manage')) Response::json(['success'=>false,'message'=>'Forbidden'],403);
        try{$rows=$this->pdo->query('SELECT a.id,a.module_name AS module,a.action_name AS action,a.change_summary AS description,a.created_at,u.full_name FROM audit_logs a LEFT JOIN users u ON u.id=a.actor_user_id ORDER BY a.id DESC LIMIT 100')->fetchAll();Response::json(['success'=>true,'data'=>$rows]);}catch(\Throwable){Response::json(['success'=>true,'data'=>[]]);}
    }
}
