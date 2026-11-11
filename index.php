<?php
require_once __DIR__ . '/config.php';

$page = $_GET['page'] ?? (logged_in() ? 'dashboard' : 'login');

$modules = [
 'residents' => ['title'=>'Residents','table'=>'residents','icon'=>'bi-people','fields'=>[
   'first_name'=>'First name','middle_name'=>'Middle name','last_name'=>'Last name','birth_date'=>'Birth date','sex'=>'Sex','civil_status'=>'Civil status','occupation'=>'Occupation','contact_number'=>'Contact number','email'=>'Email','purok'=>'Purok','address'=>'Address']],
 'households' => ['title'=>'Households','table'=>'households','icon'=>'bi-houses','fields'=>[
   'household_no'=>'Household number','head_name'=>'Household head','purok'=>'Purok','address'=>'Address','members_count'=>'Number of members','contact_number'=>'Contact number']],
 'certificates' => ['title'=>'Certificates & Clearances','table'=>'certificates','icon'=>'bi-file-earmark-text','fields'=>[
   'resident_name'=>'Resident name','certificate_type'=>'Certificate type','purpose'=>'Purpose','date_requested'=>'Date requested','status'=>'Status','amount'=>'Fee','released_at'=>'Date released']],
 'finance' => ['title'=>'Financial Records','table'=>'finance','icon'=>'bi-wallet2','fields'=>[
   'transaction_date'=>'Date','transaction_type'=>'Type','category'=>'Category','description'=>'Description','amount'=>'Amount','recorded_by'=>'Recorded by']],
 'blotter' => ['title'=>'Blotter Records','table'=>'blotter','icon'=>'bi-journal-text','fields'=>[
   'incident_date'=>'Incident date','complainant'=>'Complainant','respondent'=>'Respondent','incident_type'=>'Incident type','location'=>'Location','narrative'=>'Incident details','status'=>'Status']],
 'ordinances' => ['title'=>'Ordinances & Resolutions','table'=>'ordinances','icon'=>'bi-bank','fields'=>[
   'reference_no'=>'Reference number','document_type'=>'Document type','title'=>'Title','date_approved'=>'Date approved','status'=>'Status','description'=>'Description']],
 'inventory' => ['title'=>'Asset Inventory','table'=>'inventory','icon'=>'bi-box-seam','fields'=>[
   'asset_name'=>'Asset name','category'=>'Category','quantity'=>'Quantity','condition_status'=>'Condition','location'=>'Location','acquired_date'=>'Acquired date','estimated_value'=>'Estimated value']]
];

if ($page === 'logout') { session_destroy(); header('Location: index.php?page=login'); exit; }

if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('SELECT id, full_name, password_hash FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([trim($_POST['username'] ?? '')]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id']; $_SESSION['full_name'] = $user['full_name'];
        header('Location: index.php'); exit;
    }
    $login_error = 'Incorrect username or password.';
}

if ($page !== 'login') require_login();

if (isset($modules[$page]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $m = $modules[$page]; $table = $m['table'];
    if (($_POST['action'] ?? '') === 'delete') {
        $stmt = db()->prepare("DELETE FROM `$table` WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
    } else {
        $values = [];
        foreach ($m['fields'] as $field => $label) {
            $v = trim($_POST[$field] ?? '');
            $values[$field] = $v === '' ? null : $v;
        }
        if (!empty($_POST['id'])) {
            $sets = implode(', ', array_map(fn($f)=>"`$f` = ?", array_keys($values)));
            $stmt = db()->prepare("UPDATE `$table` SET $sets WHERE id = ?");
            $stmt->execute([...array_values($values), (int)$_POST['id']]);
        } else {
            $cols = implode(', ', array_map(fn($f)=>"`$f`", array_keys($values)));
            $marks = implode(', ', array_fill(0, count($values), '?'));
            $stmt = db()->prepare("INSERT INTO `$table` ($cols) VALUES ($marks)");
            $stmt->execute(array_values($values));
        }
    }
    header("Location: index.php?page=$page&saved=1"); exit;
}

function count_table(string $table): int {
    return (int)db()->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
function money($n): string { return '₱' . number_format((float)$n, 2); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Barangay Information System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php if ($page === 'login'): ?>
<div class="login-shell">
 <div class="login-card">
  <div class="brand-mark"><i class="bi bi-buildings"></i></div>
  <div class="text-center mb-4"><h3 class="fw-bold mb-1">Barangay Information System</h3><p class="text-secondary">One system. Better data. Stronger community.</p></div>
  <?php if (!empty($login_error)): ?><div class="alert alert-danger"><?=e($login_error)?></div><?php endif; ?>
  <form method="post">
   <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
   <label class="form-label">Username</label><input class="form-control mb-3" name="username" required autofocus>
   <label class="form-label">Password</label><input class="form-control mb-4" type="password" name="password" required>
   <button class="btn btn-primary w-100 py-2">Sign in <i class="bi bi-arrow-right ms-1"></i></button>
  </form>
  <div class="small text-secondary text-center mt-3">Initial login: <b>admin</b> / <b>admin123</b></div>
 </div>
</div>
<?php else: ?>
<div class="app-shell">
<aside class="sidebar" id="sidebar">
 <a class="brand" href="index.php"><span class="brand-mark small"><i class="bi bi-buildings"></i></span><span><b>BARANGAY</b><small>INFORMATION SYSTEM</small></span></a>
 <div class="nav-label">MAIN MENU</div>
 <a class="nav-link <?= $page==='dashboard'?'active':'' ?>" href="index.php"><i class="bi bi-grid-1x2"></i> Dashboard</a>
 <?php foreach ($modules as $key=>$m): ?>
 <a class="nav-link <?= $page===$key?'active':'' ?>" href="index.php?page=<?=$key?>"><i class="bi <?=$m['icon']?>"></i> <?=$m['title']?></a>
 <?php endforeach; ?>
 <div class="nav-label mt-4">SYSTEM</div>
 <a class="nav-link" href="index.php?page=reports"><i class="bi bi-bar-chart-line"></i> Reports & Analytics</a>
 <a class="nav-link" href="index.php?page=settings"><i class="bi bi-gear"></i> Settings</a>
 <div class="sidebar-bottom"><div class="user-avatar"><?=e(strtoupper(substr($_SESSION['full_name'] ?? 'A',0,1)))?></div><div class="user-info"><b><?=e($_SESSION['full_name'] ?? 'Administrator')?></b><small>Administrator</small></div><a class="logout" href="index.php?page=logout" title="Sign out"><i class="bi bi-box-arrow-right"></i></a></div>
</aside>
<main class="main-content">
<header class="topbar"><button class="btn btn-light d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="bi bi-list"></i></button><div><span class="text-secondary small">Pages</span><div class="fw-semibold"><?= $page==='dashboard'?'Dashboard':e($modules[$page]['title'] ?? ucfirst($page)) ?></div></div><div class="ms-auto d-flex align-items-center gap-3"><span class="text-secondary small d-none d-sm-inline"><?=date('l, F j, Y')?></span><span class="avatar-circle"><i class="bi bi-person"></i></span></div></header>
<div class="content-wrap">
<?php if ($page === 'dashboard'): ?>
 <div class="d-flex flex-wrap justify-content-between align-items-end mb-4"><div><div class="eyebrow">OVERVIEW</div><h2 class="page-title">Good day, <?=e($_SESSION['full_name'] ?? 'Administrator')?>!</h2><p class="text-secondary mb-0">Here's what's happening in your barangay today.</p></div><span class="date-chip"><i class="bi bi-calendar3 me-2"></i><?=date('M d, Y')?></span></div>
 <div class="row g-3 mb-4">
 <?php $cards=[['Total Residents',count_table('residents'),'bi-people','blue'],['Households',count_table('households'),'bi-houses','green'],['Certificate Requests',count_table('certificates'),'bi-file-earmark-check','orange'],['Blotter Records',count_table('blotter'),'bi-journal-text','red']]; foreach($cards as $c): ?>
 <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><span><?=e($c[0])?></span><span class="stat-icon <?=$c[3]?>"><i class="bi <?=$c[2]?>"></i></span></div><div class="stat-value"><?=number_format($c[1])?></div><div class="stat-foot">Recorded in the system</div></div></div>
 <?php endforeach; ?>
 </div>
 <div class="row g-3 mb-4">
  <div class="col-xl-7"><div class="panel h-100"><div class="panel-heading"><div><h5>Community Overview</h5><p>Resident records by sex</p></div><span class="panel-icon"><i class="bi bi-pie-chart"></i></span></div><div class="chart-area"><canvas id="residentChart"></canvas></div></div></div>
  <div class="col-xl-5"><div class="panel h-100"><div class="panel-heading"><div><h5>Financial Snapshot</h5><p>Recorded income and expenses</p></div><span class="panel-icon"><i class="bi bi-wallet2"></i></span></div>
  <?php $income=(float)db()->query("SELECT COALESCE(SUM(amount),0) FROM finance WHERE transaction_type='Income'")->fetchColumn(); $expense=(float)db()->query("SELECT COALESCE(SUM(amount),0) FROM finance WHERE transaction_type='Expense'")->fetchColumn(); ?>
  <div class="finance-balance"><small>Net balance</small><h2><?=money($income-$expense)?></h2></div><div class="d-flex justify-content-between border-top pt-3 mt-3"><span class="text-secondary">Income</span><b class="text-success"><?=money($income)?></b></div><div class="d-flex justify-content-between pt-2"><span class="text-secondary">Expenses</span><b class="text-danger"><?=money($expense)?></b></div></div></div>
 </div>
 <div class="row g-3">
  <div class="col-xl-7"><div class="panel"><div class="panel-heading"><div><h5>Recent Residents</h5><p>Latest resident profiles added</p></div><a class="btn btn-sm btn-outline-primary" href="index.php?page=residents">View all</a></div>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Resident</th><th>Sex</th><th>Purok</th><th>Contact</th></tr></thead><tbody>
  <?php foreach(db()->query("SELECT * FROM residents ORDER BY id DESC LIMIT 5") as $r): ?><tr><td class="fw-semibold"><?=e($r['first_name'].' '.$r['last_name'])?></td><td><?=e($r['sex'])?></td><td><?=e($r['purok'])?></td><td><?=e($r['contact_number'])?></td></tr><?php endforeach; ?>
  </tbody></table></div></div></div>
  <div class="col-xl-5"><div class="panel"><div class="panel-heading"><div><h5>Quick Actions</h5><p>Common barangay transactions</p></div></div><div class="quick-grid">
  <a href="index.php?page=residents"><i class="bi bi-person-plus"></i><span>Add resident</span></a><a href="index.php?page=certificates"><i class="bi bi-file-earmark-plus"></i><span>New certificate</span></a><a href="index.php?page=blotter"><i class="bi bi-clipboard-plus"></i><span>Record incident</span></a><a href="index.php?page=finance"><i class="bi bi-cash-stack"></i><span>Add transaction</span></a>
  </div></div></div>
 </div>
<?php elseif (isset($modules[$page])): $m=$modules[$page]; $rows=db()->query("SELECT * FROM `{$m['table']}` ORDER BY id DESC LIMIT 500")->fetchAll(); ?>
 <div class="d-flex flex-wrap justify-content-between align-items-end mb-4"><div><div class="eyebrow">RECORD MANAGEMENT</div><h2 class="page-title"><?=e($m['title'])?></h2><p class="text-secondary mb-0">Create, view, update and manage barangay records.</p></div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordModal" onclick="newRecord()"><i class="bi bi-plus-lg me-1"></i> Add New Record</button></div>
 <?php if(isset($_GET['saved'])): ?><div class="alert alert-success">Record saved successfully.</div><?php endif; ?>
 <div class="panel"><div class="panel-heading"><div><h5><?=e($m['title'])?> List</h5><p><?=count($rows)?> record(s) displayed</p></div><div class="search-box"><i class="bi bi-search"></i><input id="tableSearch" placeholder="Search records..."></div></div>
 <div class="table-responsive"><table class="table align-middle data-table"><thead><tr><th>#</th><?php foreach($m['fields'] as $label): ?><th><?=e($label)?></th><?php endforeach; ?><th>Actions</th></tr></thead><tbody>
 <?php foreach($rows as $r): ?><tr><td><?=$r['id']?></td><?php foreach($m['fields'] as $f=>$label): ?><td><?=e($r[$f] ?? '')?></td><?php endforeach; ?><td class="text-nowrap"><button class="btn btn-sm btn-light text-primary" onclick='editRecord(<?=json_encode($r,JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP)?>)'><i class="bi bi-pencil"></i></button> <form method="post" class="d-inline" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?>
 <?php if(!$rows): ?><tr><td colspan="<?=count($m['fields'])+2?>" class="text-center text-secondary py-5">No records yet. Select “Add New Record” to get started.</td></tr><?php endif; ?>
 </tbody></table></div></div>
 <div class="modal fade" id="recordModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form method="post"><div class="modal-header"><h5 class="modal-title" id="modalTitle">Add Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" id="recordId">
 <div class="row g-3"><?php foreach($m['fields'] as $f=>$label): ?><div class="col-md-6"><label class="form-label"><?=e($label)?></label>
 <?php if(in_array($f,['narrative','description','address','purpose'])): ?><textarea class="form-control" name="<?=e($f)?>" id="field_<?=e($f)?>" rows="2"></textarea>
 <?php elseif(in_array($f,['sex','civil_status','status','transaction_type','certificate_type','document_type','condition_status'])): ?><select class="form-select" name="<?=e($f)?>" id="field_<?=e($f)?>"><option value="">Select...</option><?php $opts=['sex'=>['Male','Female'],'civil_status'=>['Single','Married','Widowed','Separated'],'status'=>['Pending','Approved','Released','Resolved','Under Investigation','Active','Inactive'],'transaction_type'=>['Income','Expense'],'certificate_type'=>['Barangay Clearance','Certificate of Indigency','Certificate of Residency','Business Clearance','Other'],'document_type'=>['Ordinance','Resolution'],'condition_status'=>['Good','Fair','Needs Repair','Unserviceable']]; foreach($opts[$f]??[] as $o): ?><option><?=e($o)?></option><?php endforeach; ?></select>
 <?php else: ?><input class="form-control" name="<?=e($f)?>" id="field_<?=e($f)?>" type="<?=str_contains($f,'date')||$f==='released_at'?'date':(in_array($f,['amount','quantity','members_count','estimated_value'])?'number':'text')?>" <?=in_array($f,['amount','quantity','members_count','estimated_value'])?'step="0.01"':''?>>
 <?php endif; ?></div><?php endforeach; ?></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Record</button></div></form></div></div></div>
 <script>
 const fieldNames = <?=json_encode(array_keys($m['fields']))?>;
 function newRecord(){document.getElementById('modalTitle').textContent='Add Record';document.getElementById('recordId').value='';fieldNames.forEach(f=>{const el=document.getElementById('field_'+f);if(el)el.value='';});}
 function editRecord(row){document.getElementById('modalTitle').textContent='Edit Record';document.getElementById('recordId').value=row.id;fieldNames.forEach(f=>{const el=document.getElementById('field_'+f);if(el)el.value=row[f]??'';});bootstrap.Modal.getOrCreateInstance(document.getElementById('recordModal')).show();}
 </script>
<?php elseif ($page==='reports'): ?>
 <div class="mb-4"><div class="eyebrow">DATA & INSIGHTS</div><h2 class="page-title">Reports & Analytics</h2><p class="text-secondary">Summary of records currently stored in the system.</p></div>
 <div class="row g-3"><?php foreach($modules as $key=>$m): ?><div class="col-sm-6 col-xl-4"><div class="stat-card"><div class="stat-top"><span><?=e($m['title'])?></span><span class="stat-icon blue"><i class="bi <?=$m['icon']?>"></i></span></div><div class="stat-value"><?=number_format(count_table($m['table']))?></div><a class="small" href="index.php?page=<?=$key?>">Open records <i class="bi bi-arrow-right"></i></a></div></div><?php endforeach; ?></div>
 <div class="panel mt-3"><div class="panel-heading"><div><h5>Export data</h5><p>Download a CSV file for spreadsheet analysis.</p></div></div><div class="d-flex flex-wrap gap-2"><?php foreach($modules as $key=>$m): ?><a class="btn btn-outline-primary btn-sm" href="export.php?table=<?=$key?>"><i class="bi bi-download me-1"></i><?=e($m['title'])?></a><?php endforeach; ?></div></div>
<?php else: ?>
 <div class="panel"><h4><?=e(ucfirst($page))?></h4><p class="text-secondary mb-0">System settings and account administration can be configured here. Keep administrator credentials secure.</p></div>
<?php endif; ?>
<footer class="footer-note">Barangay Information System <span>•</span> Local management portal</footer>
</div></main></div>
<?php if($page==='dashboard'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const sexData = <?=json_encode(db()->query("SELECT COALESCE(NULLIF(sex,''),'Not specified') label, COUNT(*) total FROM residents GROUP BY sex")->fetchAll())?>;
new Chart(document.getElementById('residentChart'),{type:'doughnut',data:{labels:sexData.map(x=>x.label),datasets:[{data:sexData.map(x=>Number(x.total)),backgroundColor:['#2864d7','#31b69a','#f2a93b','#9aa7b8'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}});
</script>
<?php endif; ?>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
</body></html>
