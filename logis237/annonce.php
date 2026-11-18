<?php
require 'config/core.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare('SELECT a.*,v.nom ville_nom,q.nom quartier_nom,u.nom proprio,u.telephone tel FROM annonces a JOIN villes v ON v.id=a.ville_id JOIN quartiers q ON q.id=a.quartier_id JOIN utilisateurs u ON u.id=a.utilisateur_id WHERE a.id=?');$s->execute([$id]);$a=$s->fetch();
if(!$a||($a['statut']!=='en_ligne'&&!isAdmin()&&uid()!==(int)$a['utilisateur_id'])){http_response_code(404);head(t('notfound'));echo '<div class="wrap"><p class="empty">'.t('notfound').'</p><p style="text-align:center"><a class="btn" href="index.php">'.t('back').'</a></p></div>';foot();exit;}
$s=$pdo->prepare('SELECT chemin FROM photos WHERE annonce_id=? ORDER BY ordre');$s->execute([$id]);$ph=$s->fetchAll(PDO::FETCH_COLUMN);
$wa='https://wa.me/237'.preg_replace('/\D/','',$a['tel']).'?text='.rawurlencode(t('wamsg',$a['titre'],$a['quartier_nom']));
head($a['titre']);?>
<div class="wrap"><a class="back" href="index.php"><?=t('back')?></a>
<article class="fc det">
<div class="gal big"><?php foreach($ph?:['assets/img/placeholder.svg'] as $p):?><img src="<?=e($p)?>" alt="<?=e($a['titre'])?>" loading="lazy"><?php endforeach?></div>
<p><span class="bdg s"><?=t($a['type_transaction']==='location'?'rent':'buy')?></span> <span class="st <?=$a['statut']?>"><?=t('st_'.$a['statut'])?></span></p>
<h1><?=e($a['titre'])?></h1>
<p class="loc">📍 <?=e($a['ville_nom'])?> › <?=e($a['quartier_nom'])?><?=$a['secteur']?' · '.e($a['secteur']):''?></p>
<div class="price big"><?=fcfa($a['prix'])?><?=$a['type_transaction']==='location'?' <small>'.t('pm').'</small>':''?></div>
<div class="chips"><span class="chip"><?=t('tb_'.$a['type_bien'])?></span><?php if($a['caution']>0):?><span class="chip"><?=t('deposit')?> : <?=fcfa($a['caution'])?></span><?php endif?><?php if($a['superficie']):?><span class="chip"><?=(int)$a['superficie']?> m²</span><?php endif?><?php if($a['type_bien']!=='terrain'):?><span class="chip">🚿 <?=(int)$a['douches']?> <?=t('shower')?></span><?php endif?></div>
<?php if($a['equipements']):?><h3><?=t('amen')?></h3><div class="chips"><?php foreach(explode(',',$a['equipements']) as $x):?><span class="chip ok2">✓ <?=e(trim($x))?></span><?php endforeach?></div><?php endif?>
<h3><?=t('desc')?></h3><p class="pre"><?=e($a['description'])?></p>
<div class="box"><b><?=t('pay')?></b><div class="pays" style="border:0;padding:8px 0 0"><?php if($a['accepte_om'])echo '<span class="om">Orange Money</span>';if($a['accepte_momo'])echo '<span class="mo">MTN MoMo</span>';if(!$a['accepte_om']&&!$a['accepte_momo'])echo e(t('cash'))?></div><small><?=t('payhid')?></small></div>
<?php if($a['statut']==='en_ligne'):?><a class="btn w full" target="_blank" rel="noopener" href="<?=e($wa)?>">💬 <?=t('wa')?> — <?=e($a['proprio'])?></a><?php endif?>
<p class="warn"><?=t('scam')?></p>
</article></div>
<?php foot();
