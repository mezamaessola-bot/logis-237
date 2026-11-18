<?php
require 'config/core.php';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){chk();
 $nom=trim($_POST['nom']??'');$tl=tel($_POST['tel']??'');$pw=(string)($_POST['pw']??'');$om=tel($_POST['om']??'');$mo=tel($_POST['mo']??'');
 if($nom==='')$err=t('err_req');elseif(!preg_match('/^6\d{8}$/',$tl))$err=t('err_phone');elseif(strlen($pw)<6)$err=t('err_pw');
 else{$s=$pdo->prepare('SELECT 1 FROM utilisateurs WHERE telephone=?');$s->execute([$tl]);
  if($s->fetch())$err=t('err_taken');
  else{$pdo->prepare('INSERT INTO utilisateurs(nom,telephone,mot_de_passe,num_om,num_momo) VALUES(?,?,?,?,?)')->execute([$nom,$tl,password_hash($pw,PASSWORD_DEFAULT),preg_match('/^6\d{8}$/',$om)?$om:null,preg_match('/^6\d{8}$/',$mo)?$mo:null]);
   session_regenerate_id(true);$_SESSION['uid']=(int)$pdo->lastInsertId();$_SESSION['role']='proprietaire';$_SESSION['nom']=$nom;header('Location: dashboard.php');exit;}}}
head(t('register'));?>
<div class="wrap"><form method="post" class="fc"><h2><?=t('reg_t')?></h2><?php if($err)echo '<p class="err">'.e($err).'</p>'?><?=field()?>
<div class="f"><label><?=t('name')?> *</label><input name="nom" value="<?=e($_POST['nom']??'')?>" required></div>
<div class="f"><label><?=t('phone')?> *</label><input type="tel" name="tel" inputmode="numeric" placeholder="699000000" value="<?=e($_POST['tel']??'')?>" required></div>
<div class="f"><label><?=t('pass')?> *</label><input type="password" name="pw" autocomplete="new-password" required></div>
<div class="two"><div class="f"><label><?=t('omn')?></label><input type="tel" name="om" inputmode="numeric" placeholder="69XXXXXXX"></div><div class="f"><label><?=t('momn')?></label><input type="tel" name="mo" inputmode="numeric" placeholder="67XXXXXXX"></div></div>
<button class="btn o full"><?=t('signup')?></button><p class="alt"><?=t('hasacc')?> <a href="connexion.php"><?=t('login')?></a></p></form></div>
<?php foot();
