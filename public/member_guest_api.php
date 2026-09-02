<?php

declare(strict_types=1);

$config = require __DIR__ . '/../app/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'Africa/Dar_es_Salaam');

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';

use App\Core\Auth;
use App\Core\Database;

header('Content-Type: application/json; charset=utf-8');

function apiJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

Auth::boot($config);

if (!Auth::check()) {
    apiJson(['success' => false, 'message' => 'Unauthenticated'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    $csrf = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!Auth::validateCsrfToken($csrf)) {
        apiJson(['success' => false, 'message' => 'Invalid CSRF token'], 403);
    }
}

try {
    $pdo = Database::connection($config);
} catch (Throwable $e) {
    apiJson(['success' => false, 'message' => 'Database connection failed'], 500);
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$input = json_decode((string) file_get_contents('php://input'), true);
$input = is_array($input) ? $input : $_POST;

function requirePermission(string $permission): void
{
    if (!Auth::can($permission)) {
        apiJson(['success' => false, 'message' => 'You do not have permission to perform this action'], 403);
    }
}

function nullable(array $input, string $key): ?string
{
    $value = $input[$key] ?? null;
    return ($value === null || trim((string) $value) === '') ? null : trim((string) $value);
}

/* ---------------- Members ---------------- */
if ($method === 'GET' && $uri === '/api/v1/members') {
    $search = trim((string) ($_GET['search'] ?? ''));
    $status = trim((string) ($_GET['status'] ?? ''));
    $gender = trim((string) ($_GET['gender'] ?? ''));
    $region = trim((string) ($_GET['region'] ?? ''));

    $sql = 'SELECT id, member_code, first_name, last_name, gender, date_of_birth,
                   marital_status, phone, alt_phone, email, physical_address, ward,
                   district, region, emergency_contact_name, emergency_contact_phone,
                   baptism_date, join_date, member_status, notes
            FROM members WHERE 1=1';
    $params = [];

    if ($search !== '') {
        $sql .= ' AND (first_name LIKE :s OR last_name LIKE :s2 OR phone LIKE :s3 OR member_code LIKE :s4 OR email LIKE :s5)';
        $like = '%' . $search . '%';
        $params += [':s' => $like, ':s2' => $like, ':s3' => $like, ':s4' => $like, ':s5' => $like];
    }
    if (in_array($status, ['active','inactive','transferred','deceased'], true)) {
        $sql .= ' AND member_status = :status';
        $params[':status'] = $status;
    }
    if (in_array($gender, ['male','female','other'], true)) {
        $sql .= ' AND gender = :gender';
        $params[':gender'] = $gender;
    }
    if ($region !== '') {
        $sql .= ' AND region = :region';
        $params[':region'] = $region;
    }
    $sql .= ' ORDER BY id DESC LIMIT 1000';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    apiJson(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($method === 'GET' && $uri === '/api/v1/members/stats') {
    $row = $pdo->query("SELECT COUNT(*) total,
        COALESCE(SUM(member_status='active'),0) active,
        COALESCE(SUM(member_status='inactive'),0) inactive,
        COALESCE(SUM(member_status='transferred'),0) transferred,
        COALESCE(SUM(member_status='deceased'),0) deceased,
        COALESCE(SUM(gender='male'),0) male,
        COALESCE(SUM(gender='female'),0) female
        FROM members")->fetch(PDO::FETCH_ASSOC);
    apiJson(['success' => true, 'data' => $row ?: []]);
}

if ($method === 'POST' && $uri === '/api/v1/members') {
    requirePermission('members.create');
    foreach (['first_name','last_name','gender','phone'] as $field) {
        if (trim((string) ($input[$field] ?? '')) === '') {
            apiJson(['success' => false, 'message' => $field . ' is required'], 422);
        }
    }

    $gender = (string) $input['gender'];
    if (!in_array($gender, ['male','female','other'], true)) {
        apiJson(['success' => false, 'message' => 'Invalid gender'], 422);
    }
    $status = (string) ($input['member_status'] ?? 'active');
    if (!in_array($status, ['active','inactive','transferred','deceased'], true)) $status = 'active';

    $code = trim((string) ($input['member_code'] ?? ''));
    if ($code === '') {
        $code = (string) $pdo->query("SELECT CONCAT('MBR-', DATE_FORMAT(NOW(),'%Y'), '-', LPAD(COALESCE(MAX(id),0)+1,4,'0')) FROM members")->fetchColumn();
    }
    $joinDate = nullable($input, 'join_date') ?: date('Y-m-d');
    $actor = Auth::user()['id'] ?? null;

    $stmt = $pdo->prepare('INSERT INTO members
        (member_code, first_name, last_name, gender, date_of_birth, marital_status,
         phone, alt_phone, email, physical_address, ward, district, region,
         emergency_contact_name, emergency_contact_phone, baptism_date, join_date,
         member_status, notes, created_by, updated_by)
        VALUES (:code,:first,:last,:gender,:dob,:marital,:phone,:alt,:email,:address,:ward,:district,:region,
                :emergency_name,:emergency_phone,:baptism,:join_date,:status,:notes,:created_by,:updated_by)');
    $stmt->execute([
        ':code'=>$code, ':first'=>trim((string)$input['first_name']), ':last'=>trim((string)$input['last_name']),
        ':gender'=>$gender, ':dob'=>nullable($input,'date_of_birth'), ':marital'=>nullable($input,'marital_status'),
        ':phone'=>trim((string)$input['phone']), ':alt'=>nullable($input,'alt_phone'), ':email'=>nullable($input,'email'),
        ':address'=>nullable($input,'physical_address'), ':ward'=>nullable($input,'ward'), ':district'=>nullable($input,'district'),
        ':region'=>nullable($input,'region'), ':emergency_name'=>nullable($input,'emergency_contact_name'),
        ':emergency_phone'=>nullable($input,'emergency_contact_phone'), ':baptism'=>nullable($input,'baptism_date'),
        ':join_date'=>$joinDate, ':status'=>$status, ':notes'=>nullable($input,'notes'),
        ':created_by'=>$actor, ':updated_by'=>$actor,
    ]);
    apiJson(['success'=>true,'message'=>'Member created','data'=>['id'=>(int)$pdo->lastInsertId(),'member_code'=>$code]],201);
}

if ($method === 'PUT' && preg_match('#^/api/v1/members/(\\d+)$#', $uri, $m)) {
    requirePermission('members.edit');
    $id = (int)$m[1];
    $allowed = ['first_name','last_name','gender','date_of_birth','marital_status','phone','alt_phone','email',
        'physical_address','ward','district','region','emergency_contact_name','emergency_contact_phone',
        'baptism_date','join_date','member_status','notes'];
    $sets=[]; $params=[':id'=>$id];
    foreach($allowed as $field){
        if(array_key_exists($field,$input)){
            if($field==='gender' && !in_array($input[$field],['male','female','other'],true)) apiJson(['success'=>false,'message'=>'Invalid gender'],422);
            if($field==='member_status' && !in_array($input[$field],['active','inactive','transferred','deceased'],true)) apiJson(['success'=>false,'message'=>'Invalid status'],422);
            $sets[]="`$field` = :$field";
            $params[":$field"] = ($input[$field] === '' ? null : $input[$field]);
        }
    }
    if(!$sets) apiJson(['success'=>false,'message'=>'Nothing to update'],422);
    $sets[]='updated_by = :updated_by'; $params[':updated_by']=Auth::user()['id'] ?? null;
    $stmt=$pdo->prepare('UPDATE members SET '.implode(',',$sets).' WHERE id=:id');
    $stmt->execute($params);
    apiJson(['success'=>true,'message'=>'Member updated']);
}

if ($method === 'DELETE' && preg_match('#^/api/v1/members/(\\d+)$#', $uri, $m)) {
    requirePermission('members.delete');
    $stmt=$pdo->prepare('DELETE FROM members WHERE id=:id');
    $stmt->execute([':id'=>(int)$m[1]]);
    apiJson(['success'=>true,'message'=>'Member deleted']);
}

/* ---------------- Guests ---------------- */
if ($method === 'GET' && $uri === '/api/v1/attendance/guests') {
    $search=trim((string)($_GET['search']??'')); $status=trim((string)($_GET['status']??''));
    $sql='SELECT id,guest_code,first_name,last_name,phone,location,service_date,visit_type,email,age_group,notes,status,follow_up_date,invited_by_name,created_at FROM guests WHERE 1=1';
    $params=[];
    if($search!==''){
        $sql.=' AND (guest_code LIKE :s OR first_name LIKE :s2 OR last_name LIKE :s3 OR phone LIKE :s4 OR email LIKE :s5 OR location LIKE :s6)';
        $like='%'.$search.'%'; $params += [':s'=>$like,':s2'=>$like,':s3'=>$like,':s4'=>$like,':s5'=>$like,':s6'=>$like];
    }
    if(in_array($status,['registered','visited','converted','inactive'],true)){ $sql.=' AND status=:status'; $params[':status']=$status; }
    $sql.=' ORDER BY service_date DESC,id DESC LIMIT 1000';
    $stmt=$pdo->prepare($sql); $stmt->execute($params);
    apiJson(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($method === 'POST' && $uri === '/api/v1/attendance/register-guest') {
    requirePermission('members.create');
    foreach(['first_name','last_name','phone','location'] as $field){ if(trim((string)($input[$field]??''))==='') apiJson(['success'=>false,'message'=>$field.' is required'],422); }
    $visit=(string)($input['visit_type']??'first_time'); if(!in_array($visit,['first_time','returning','referred'],true)) $visit='first_time';
    $status=(string)($input['status']??'registered'); if(!in_array($status,['registered','visited','converted','inactive'],true)) $status='registered';
    $code=(string)$pdo->query("SELECT CONCAT('GST-',DATE_FORMAT(NOW(),'%Y'),'-',LPAD(COALESCE(MAX(id),0)+1,4,'0')) FROM guests")->fetchColumn();
    $stmt=$pdo->prepare('INSERT INTO guests (guest_code,first_name,last_name,phone,location,service_date,visit_type,email,age_group,notes,status,follow_up_date,invited_by_name,created_by) VALUES (:code,:first,:last,:phone,:location,:service,:visit,:email,:age,:notes,:status,:follow,:invited,:created)');
    $stmt->execute([
        ':code'=>$code,':first'=>trim((string)$input['first_name']),':last'=>trim((string)$input['last_name']),':phone'=>trim((string)$input['phone']),
        ':location'=>trim((string)$input['location']),':service'=>nullable($input,'service_date')?:date('Y-m-d'),':visit'=>$visit,':email'=>nullable($input,'email'),
        ':age'=>nullable($input,'age_group'),':notes'=>nullable($input,'notes'),':status'=>$status,':follow'=>nullable($input,'follow_up_date'),
        ':invited'=>nullable($input,'invited_by_name'),':created'=>Auth::user()['id']??null,
    ]);
    apiJson(['success'=>true,'message'=>'Guest registered','data'=>['id'=>(int)$pdo->lastInsertId(),'guest_code'=>$code]],201);
}

if ($method === 'PUT' && preg_match('#^/api/v1/attendance/guests/(\\d+)$#', $uri, $m)) {
    requirePermission('members.edit'); $id=(int)$m[1];
    $sets=[];$params=[':id'=>$id];
    if(array_key_exists('status',$input)){ if(!in_array($input['status'],['registered','visited','converted','inactive'],true)) apiJson(['success'=>false,'message'=>'Invalid guest status'],422); $sets[]='status=:status';$params[':status']=$input['status']; }
    if(array_key_exists('follow_up_date',$input)){ $sets[]='follow_up_date=:follow';$params[':follow']=($input['follow_up_date']?:null); }
    if(array_key_exists('notes',$input)){ $sets[]='notes=:notes';$params[':notes']=($input['notes']?:null); }
    if(!$sets) apiJson(['success'=>false,'message'=>'Nothing to update'],422);
    $stmt=$pdo->prepare('UPDATE guests SET '.implode(',',$sets).' WHERE id=:id');$stmt->execute($params);
    apiJson(['success'=>true,'message'=>'Guest updated']);
}

apiJson(['success'=>false,'message'=>'Endpoint not found'],404);
