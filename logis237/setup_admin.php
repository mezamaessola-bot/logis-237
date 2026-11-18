<?php
require 'config/core.php';
$id=$pdo->query("SELECT id FROM utilisateurs WHERE role='admin' AND mot_de_passe='!' LIMIT 1")->fetchColumn();
$msg='';
if($id&&$_SERVER['REQUEST_METHOD']==='POST'){chk();$tl=tel($_POST['tel']??'');$pw=(string)($_POST['pw']??'');$nom=trim($_POST['nom']??'')?:'Administrateur';
 if(!preg_match('/^6\d{8}$/',$tl)||strlen($pw)<8)$msg='Numéro invalide ou mot de passe trop court (8 caractères minimum).';
 else{$pdo->prepare('UPDATE utilisateurs SET nom=?,telephone=?,mot_de_passe=? WHERE id=?')->execute([$nom,$tl,password_hash($pw,PASSWORD_DEFAULT),$id]);$id=0;$msg='Compte administrateur créé. Supprimez maintenant setup_admin.php puis connectez-vous.';}}
head('Administrateur');?>
<div class="wrap"><div class="fc"><h2>Créer l’administrateur</h2><?php if($msg)echo '<p class="'.($id?'err':'ok').'">'.e($msg).'</p>'?>
<?php if($id):?><form method="post"><?=field()?><div class="f"><label>Nom</label><input name="nom"></div><div class="f"><label>Téléphone</label><input name="tel" inputmode="numeric" placeholder="699000000" required></div><div class="f"><label>Mot de passe (8 car. min.)</label><input type="password" name="pw" required></div><button class="btn o full">Créer</button></form>
<?php elseif(!$msg):?><p>Un administrateur existe déjà. Supprimez ce fichier.</p><?php else:?><a class="btn full" href="connexion.php">Se connecter</a><?php endif?></div></div>
<?php foot();
