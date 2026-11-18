<?php
require 'config/core.php';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){chk();
 $s=$pdo->prepare('SELECT * FROM utilisateurs WHERE telephone=?');$s->execute([tel($_POST['tel']??'')]);$u=$s->fetch();
 if($u&&$u['bloque_jusqua']&&strtotime($u['bloque_jusqua'])>time())$err=t('err_lock');
 elseif($u&&password_verify((string)($_POST['pw']??''),$u['mot_de_passe'])){
  $pdo->prepare('UPDATE utilisateurs SET tentatives=0,bloque_jusqua=NULL WHERE id=?')->execute([$u['id']]);
  session_regenerate_id(true);$_SESSION['uid']=(int)$u['id'];$_SESSION['role']=$u['role'];$_SESSION['nom']=$u['nom'];
  header('Location: '.($u['role']==='admin'?'admin.php':'dashboard.php'));exit;}
 else{if($u){$n=$u['tentatives']+1;$pdo->prepare('UPDATE utilisateurs SET tentatives=?,bloque_jusqua=? WHERE id=?')->execute([$n>=5?0:$n,$n>=5?date('Y-m-d H:i:s',time()+900):null,$u['id']]);}$err=t('err_bad');}}
head(t('login'));?>
<div class="wrap"><form method="post" class="fc"><h2><?=t('login')?></h2><?php if($err)echo '<p class="err">'.e($err).'</p>'?><?=field()?>
<div class="f"><label><?=t('phone')?></label><input type="tel" name="tel" inputmode="numeric" autocomplete="username" required autofocus></div>
<div class="f"><label><?=t('pass')?></label><input type="password" name="pw" autocomplete="current-password" required></div>
<button class="btn o full"><?=t('login')?></button><p class="alt"><?=t('noacc')?> <a href="inscription.php"><?=t('signup')?></a></p></form></div>
<?php foot();
