<?php
require 'config/core.php';needLogin();
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$id=(int)($_POST['id']??0);
 $s=$pdo->prepare('SELECT statut FROM annonces WHERE id=? AND utilisateur_id=?');$s->execute([$id,uid()]);
 if($x=$s->fetchColumn()){if(($_POST['do']??'')==='del')delAd($id);elseif(($_POST['do']??'')==='tog'&&$x!=='conclue')$pdo->prepare('UPDATE annonces SET statut=? WHERE id=?')->execute([$x==='en_ligne'?'retiree':'en_ligne',$id]);}
 header('Location: dashboard.php');exit;}
$s=$pdo->prepare('SELECT a.*,v.nom ville_nom,q.nom quartier_nom,(SELECT chemin FROM photos WHERE annonce_id=a.id ORDER BY ordre LIMIT 1) thumb FROM annonces a JOIN villes v ON v.id=a.ville_id JOIN quartiers q ON q.id=a.quartier_id WHERE a.utilisateur_id=? ORDER BY a.date_publication DESC');$s->execute([uid()]);$rows=$s->fetchAll();
head(t('mine'));?>
<div class="wrap"><div class="top"><h2><?=t('mine')?> (<?=count($rows)?>)</h2><a class="btn o" href="publier.php"><?=t('publish')?></a></div>
<?php if(!$rows)echo '<p class="empty">'.t('nothing').'</p>'; foreach($rows as $a):?>
<div class="row"><img src="<?=e($a['thumb']?:'assets/img/placeholder.svg')?>" alt="">
<div class="i"><a href="annonce.php?id=<?=$a['id']?>"><b><?=e($a['titre'])?></b></a><br><small><?=e($a['ville_nom'])?> › <?=e($a['quartier_nom'])?> · <?=fcfa($a['prix'])?></small><br><span class="st <?=$a['statut']?>"><?=t('st_'.$a['statut'])?></span></div>
<div class="a"><a class="btn" href="publier.php?id=<?=$a['id']?>"><?=t('edit')?></a>
<?php if($a['statut']!=='conclue'):?><form method="post"><?=field()?><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn g" name="do" value="tog"><?=t($a['statut']==='en_ligne'?'withdraw':'relist')?></button></form><?php endif?>
<form method="post" onsubmit="return confirm('<?=t('confirm')?>')"><?=field()?><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn r" name="do" value="del"><?=t('del')?></button></form></div></div>
<?php endforeach?></div>
<?php foot();
