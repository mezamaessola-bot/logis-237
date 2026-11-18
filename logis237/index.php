<?php
require 'config/core.php';
$w=["a.statut='en_ligne'"];$p=[];
if(!empty($_GET['ville'])){$w[]='a.ville_id=?';$p[]=(int)$_GET['ville'];}
if(!empty($_GET['quartier'])){$w[]='a.quartier_id=?';$p[]=(int)$_GET['quartier'];}
if(($sc=trim($_GET['secteur']??''))!==''){$w[]='a.secteur LIKE ?';$p[]='%'.$sc.'%';}
if(in_array($_GET['tt']??'',['location','vente'],true)){$w[]='a.type_transaction=?';$p[]=$_GET['tt'];}
if(in_array($_GET['tb']??'',TB,true)){$w[]='a.type_bien=?';$p[]=$_GET['tb'];}
if((int)($_GET['pm']??0)>0){$w[]='a.prix<=?';$p[]=(int)$_GET['pm'];}
$W=implode(' AND ',$w);
$c=$pdo->prepare("SELECT COUNT(*) FROM annonces a WHERE $W");$c->execute($p);$n=(int)$c->fetchColumn();
$per=9;$pages=max(1,(int)ceil($n/$per));$pg=min(max(1,(int)($_GET['page']??1)),$pages);
$o='a.date_publication DESC';$po=[];
if(isset($_GET['lat'],$_GET['lng'])&&is_numeric($_GET['lat'])&&is_numeric($_GET['lng'])){$o='POW(COALESCE(a.lat,q.lat)-?,2)+POW(COALESCE(a.lng,q.lng)-?,2)';$po=[(float)$_GET['lat'],(float)$_GET['lng']];}
$st=$pdo->prepare("SELECT a.*,v.nom ville_nom,q.nom quartier_nom,q.lat qlat,q.lng qlng FROM annonces a JOIN villes v ON v.id=a.ville_id JOIN quartiers q ON q.id=a.quartier_id WHERE $W ORDER BY $o LIMIT $per OFFSET ".(($pg-1)*$per));
$st->execute(array_merge($p,$po));$rows=$st->fetchAll();
$ph=[];
if($rows){$ids=array_column($rows,'id');$s=$pdo->prepare('SELECT annonce_id,chemin FROM photos WHERE annonce_id IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY ordre');$s->execute($ids);foreach($s as $r)$ph[$r['annonce_id']][]=$r['chemin'];}
$mk=array_map(fn($a)=>['id'=>(int)$a['id'],'t'=>$a['titre'],'q'=>$a['quartier_nom'],'p'=>fcfa($a['prix']),'lat'=>(float)($a['lat']?:$a['qlat'])+(($a['id']%7)-3)*0.0009,'lng'=>(float)($a['lng']?:$a['qlng'])+(($a['id']%5)-2)*0.0009],$rows);
ob_start();
if(!$rows)echo '<p class="empty">'.t('none').'</p>';
else{echo '<div class="grid">';foreach($rows as $a)echo card($a,$ph[$a['id']]??[]);echo '</div>';
 if($pages>1){echo '<nav class="pager">';for($i=1;$i<=$pages;$i++){$q=$_GET;unset($q['ajax']);$q['page']=$i;echo '<a data-page="'.$i.'" class="'.($i===$pg?'on':'').'" href="?'.e(http_build_query($q)).'">'.$i.'</a>';}echo '</nav>';}}
$html=ob_get_clean();
if(isset($_GET['ajax'])){header('Content-Type: application/json; charset=utf-8');echo json_encode(['html'=>$html,'count'=>$n,'markers'=>$mk]);exit;}
$villes=$pdo->query('SELECT * FROM villes ORDER BY nom')->fetchAll();$quartiers=$pdo->query('SELECT * FROM quartiers ORDER BY nom')->fetchAll();
$g=fn($k)=>$_GET[$k]??'';
head(t('hero1'));?>
<section class="hero"><h1><?=t('hero1')?></h1><p><?=t('hero2')?></p></section>
<form id="search" class="search" action="index.php" method="get">
<div class="seg"><?php foreach([''=>'all','location'=>'rent','vente'=>'buy'] as $v=>$l):?><label><input type="radio" name="tt" value="<?=$v?>"<?=$g('tt')===$v?' checked':''?>><span><?=t($l)?></span></label><?php endforeach?></div>
<div class="fields">
<select name="ville" id="select-ville" aria-label="<?=t('ville')?>"><option value=""><?=t('allc')?></option><?php foreach($villes as $v):?><option value="<?=$v['id']?>"<?=sel($g('ville'),$v['id'])?>><?=e($v['nom'])?></option><?php endforeach?></select>
<select name="quartier" id="select-quartier" aria-label="<?=t('quartier')?>"><option value=""><?=t('alld')?></option><?php foreach($quartiers as $q):?><option value="<?=$q['id']?>" data-ville="<?=$q['ville_id']?>"<?=sel($g('quartier'),$q['id'])?>><?=e($q['nom'])?></option><?php endforeach?></select>
<input type="text" name="secteur" placeholder="<?=t('sector')?>" value="<?=e($g('secteur'))?>">
<select name="tb" aria-label="<?=t('type')?>"><option value=""><?=t('type')?></option><?php foreach(TB as $b):?><option value="<?=$b?>"<?=sel($g('tb'),$b)?>><?=t('tb_'.$b)?></option><?php endforeach?></select>
<input type="number" name="pm" min="0" step="5000" inputmode="numeric" placeholder="<?=t('budget')?>" value="<?=e($g('pm'))?>">
</div>
<div class="acts"><button class="btn o" type="submit"><?=t('search')?></button><button class="btn" type="button" id="btn-geoloc"><?=t('near')?></button><button class="btn g" type="button" id="toggle-map"><?=t('map')?></button></div>
</form>
<div class="wrap"><div id="map" hidden></div>
<h2 class="count"><b id="count"><?=$n?></b> <?=t('found')?></h2>
<div id="results"><?=$html?></div></div>
<script>window.MK=<?=json_encode($mk,JSON_HEX_TAG|JSON_UNESCAPED_UNICODE)?>;</script>
<?php foot();
