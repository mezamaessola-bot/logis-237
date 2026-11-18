<?php
require 'config/core.php';needLogin();
$id=(int)($_GET['id']??0);$ad=null;
if($id){$s=$pdo->prepare('SELECT * FROM annonces WHERE id=?'.(isAdmin()?'':' AND utilisateur_id='.uid()));$s->execute([$id]);$ad=$s->fetch();if(!$ad){http_response_code(404);exit(t('notfound'));}}
$taux=taux();$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){chk();
 $titre=trim($_POST['titre']??'');$tt=$_POST['tt']??'';$tb=$_POST['tb']??'';$vid=(int)($_POST['ville']??0);$qid=(int)($_POST['quartier']??0);
 $sec=trim($_POST['secteur']??'')?:null;$prix=(int)($_POST['prix']??0);$cau=max(0,(int)($_POST['caution']??0));$sup=(int)($_POST['sup']??0)?:null;$dou=max(0,(int)($_POST['dou']??1));
 $eq=implode(', ',array_intersect(EQ,(array)($_POST['eq']??[])));$desc=trim($_POST['desc']??'');$om=isset($_POST['om'])?1:0;$mo=isset($_POST['mo'])?1:0;
 $q=$pdo->prepare('SELECT 1 FROM quartiers WHERE id=? AND ville_id=?');$q->execute([$qid,$vid]);
 if($titre===''||$prix<=0||$desc===''||!in_array($tt,['location','vente'],true)||!in_array($tb,TB,true)||!$q->fetch())$err=t('err_req');
 elseif(!$ad&&empty($_POST['agree']))$err=t('err_comm');
 else{$f=[$titre,$tt,$tb,$vid,$qid,$sec,$prix,$cau,$sup,$dou,$eq,$desc,$om,$mo];
  if($ad){$pdo->prepare('UPDATE annonces SET titre=?,type_transaction=?,type_bien=?,ville_id=?,quartier_id=?,secteur=?,prix=?,caution=?,superficie=?,douches=?,equipements=?,description=?,accepte_om=?,accepte_momo=? WHERE id=?')->execute([...$f,$ad['id']]);$aid=(int)$ad['id'];}
  else{$pdo->prepare('INSERT INTO annonces(utilisateur_id,titre,type_transaction,type_bien,ville_id,quartier_id,secteur,prix,caution,superficie,douches,equipements,description,accepte_om,accepte_momo,taux_commission_accepte,date_acceptation_commission) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())')->execute([uid(),...$f,$taux]);$aid=(int)$pdo->lastInsertId();}
  $new=[];foreach(($_FILES['photos']['name']??[]) as $i=>$nm){if(count($new)>=4)break;$r=saveImg(['tmp_name'=>$_FILES['photos']['tmp_name'][$i],'size'=>$_FILES['photos']['size'][$i],'error'=>$_FILES['photos']['error'][$i]]);if($r)$new[]=$r;}
  if($new){delPhotos($aid);foreach($new as $i=>$r)$pdo->prepare('INSERT INTO photos(annonce_id,chemin,ordre) VALUES(?,?,?)')->execute([$aid,$r,$i+1]);}
  header('Location: '.(isAdmin()?'admin.php':'dashboard.php'));exit;}}
$d=$_POST?:($ad?:[]);
$v=fn($k,$x='')=>e($_POST[$k]??$ad[$k]??$x);
$cur=$_POST?(array)($_POST['eq']??[]):explode(', ',$ad['equipements']??'');
$villes=$pdo->query('SELECT * FROM villes ORDER BY nom')->fetchAll();$quartiers=$pdo->query('SELECT * FROM quartiers ORDER BY nom')->fetchAll();$secs=$pdo->query('SELECT DISTINCT nom FROM secteurs')->fetchAll(PDO::FETCH_COLUMN);
$m=fn($p,$c)=>$_POST?isset($_POST[$p]):(bool)($ad[$c]??0);
$cv=$_POST['ville']??$ad['ville_id']??1;$cq=$_POST['quartier']??$ad['quartier_id']??'';
head($ad?t('editad'):t('newad'));?>
<div class="wrap"><form method="post" enctype="multipart/form-data" class="fc"><h2><?=$ad?t('editad'):t('newad')?></h2><?php if($err)echo '<p class="err">'.e($err).'</p>'?><?=field()?>
<div class="f"><label><?=t('title')?> *</label><input name="titre" value="<?=$v('titre')?>" placeholder="Studio haut standing" required></div>
<div class="two"><div class="f"><label><?=t('trans')?></label><select name="tt"><?php foreach(['location'=>'rent','vente'=>'buy'] as $k=>$l):?><option value="<?=$k?>"<?=sel($_POST['tt']??$ad['type_transaction']??'location',$k)?>><?=t($l)?></option><?php endforeach?></select></div>
<div class="f"><label><?=t('type')?></label><select name="tb"><?php foreach(TB as $b):?><option value="<?=$b?>"<?=sel($_POST['tb']??$ad['type_bien']??'studio',$b)?>><?=t('tb_'.$b)?></option><?php endforeach?></select></div></div>
<div class="two"><div class="f"><label><?=t('ville')?></label><select name="ville" id="select-ville"><?php foreach($villes as $x):?><option value="<?=$x['id']?>"<?=sel($cv,$x['id'])?>><?=e($x['nom'])?></option><?php endforeach?></select></div>
<div class="f"><label><?=t('quartier')?></label><select name="quartier" id="select-quartier"><?php foreach($quartiers as $x):?><option value="<?=$x['id']?>" data-ville="<?=$x['ville_id']?>"<?=sel($cq,$x['id'])?>><?=e($x['nom'])?></option><?php endforeach?></select></div></div>
<div class="f"><label><?=t('sector')?></label><input name="secteur" list="secs" value="<?=$v('secteur')?>"><datalist id="secs"><?php foreach($secs as $x)echo '<option value="'.e($x).'">'?></datalist></div>
<div class="two"><div class="f"><label><?=t('price')?> *</label><input type="number" name="prix" min="1" inputmode="numeric" value="<?=$v('prix')?>" placeholder="300000" required></div><div class="f"><label><?=t('caution')?></label><input type="number" name="caution" min="0" inputmode="numeric" value="<?=$v('caution')?>" placeholder="280000"></div></div>
<div class="two"><div class="f"><label><?=t('showers')?></label><input type="number" name="dou" min="0" value="<?=$v('douches',1)?>"></div><div class="f"><label><?=t('area')?></label><input type="number" name="sup" min="0" value="<?=$v('superficie')?>"></div></div>
<div class="f"><label><?=t('amen')?></label><div class="eq"><?php foreach(EQ as $x):?><label><input type="checkbox" name="eq[]" value="<?=e($x)?>"<?=in_array($x,$cur)?' checked':''?>> <?=e($x)?></label><?php endforeach?></div></div>
<div class="f"><label><?=t('desc')?> *</label><textarea name="desc" rows="5" required><?=$v('description')?></textarea></div>
<div class="f"><label><?=t('payacc')?></label><div class="eq"><label><input type="checkbox" name="om" <?=$m('om','accepte_om')?'checked':''?>> Orange Money</label><label><input type="checkbox" name="mo" <?=$m('mo','accepte_momo')?'checked':''?>> MTN MoMo</label></div></div>
<div class="f"><label><?=t('photos')?></label><input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple><small><?=t('photohelp')?></small><div class="prev" id="prev"></div></div>
<?php if(!$ad):?><label class="agree"><input type="checkbox" name="agree" value="1" required><span><?=t('agree',$taux)?></span></label><?php endif?>
<button class="btn o full"><?=$ad?t('save'):t('pubnow')?></button></form></div>
<?php foot();
