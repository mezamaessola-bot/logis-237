<?php
require 'config/core.php';needAdmin();
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$do=$_POST['do']??'';
 if($do==='rate'){$r=(float)($_POST['taux']??-1);if($r>=0&&$r<=100)$pdo->prepare('UPDATE parametres SET taux_commission=?')->execute([$r]);}
 elseif($do==='ad'){$id=(int)$_POST['id'];$st=$_POST['st']??'';if($st==='del')delAd($id);elseif(in_array($st,['en_ligne','retiree','conclue'],true))$pdo->prepare('UPDATE annonces SET statut=? WHERE id=?')->execute([$st,$id]);}
 elseif($do==='user'){$s=$pdo->prepare("SELECT a.id FROM annonces a JOIN utilisateurs u ON u.id=a.utilisateur_id WHERE u.id=? AND u.role='proprietaire'");$s->execute([(int)$_POST['id']]);foreach($s->fetchAll(PDO::FETCH_COLUMN) as $a)delAd($a);$pdo->prepare("DELETE FROM utilisateurs WHERE id=? AND role='proprietaire'")->execute([(int)$_POST['id']]);}
 elseif($do==='ville'&&trim($_POST['nom']??'')!==''&&is_numeric($_POST['lat']??'')&&is_numeric($_POST['lng']??''))$pdo->prepare('INSERT IGNORE INTO villes(nom,lat,lng) VALUES(?,?,?)')->execute([trim($_POST['nom']),$_POST['lat'],$_POST['lng']]);
 elseif($do==='quartier'&&trim($_POST['nom']??'')!==''){$s=$pdo->prepare('SELECT lat,lng FROM villes WHERE id=?');$s->execute([(int)$_POST['ville']]);if($v=$s->fetch()){$la=is_numeric($_POST['lat']??'')&&$_POST['lat']!==''?$_POST['lat']:$v['lat'];$ln=is_numeric($_POST['lng']??'')&&$_POST['lng']!==''?$_POST['lng']:$v['lng'];$pdo->prepare('INSERT INTO quartiers(ville_id,nom,lat,lng) VALUES(?,?,?,?)')->execute([(int)$_POST['ville'],trim($_POST['nom']),$la,$ln]);}}
 header('Location: admin.php');exit;}
$one=fn($q)=>$pdo->query($q)->fetchColumn();
$gains=$one("SELECT COALESCE(SUM(prix*taux_commission_accepte/100),0) FROM annonces WHERE statut='conclue'");
$potentiel=$one("SELECT COALESCE(SUM(prix*taux_commission_accepte/100),0) FROM annonces WHERE statut='en_ligne'");
$taux=taux();$villes=$pdo->query('SELECT * FROM villes ORDER BY nom')->fetchAll();
$ads=$pdo->query('SELECT a.*,u.nom proprio,u.telephone tel,v.nom ville_nom,q.nom quartier_nom FROM annonces a JOIN utilisateurs u ON u.id=a.utilisateur_id JOIN villes v ON v.id=a.ville_id JOIN quartiers q ON q.id=a.quartier_id ORDER BY a.date_publication DESC')->fetchAll();
$us=$pdo->query("SELECT u.*,(SELECT COUNT(*) FROM annonces WHERE utilisateur_id=u.id) nb FROM utilisateurs u WHERE role='proprietaire' ORDER BY date_inscription DESC")->fetchAll();
$cf=t('confirm');
head('Administration');?>
<div class="wrap"><h2 style="margin-top:22px">Administration</h2>
<div class="stats"><div class="stat">Mes gains<b><?=fcfa($gains)?></b></div><div class="stat">Gains potentiels (en ligne)<b><?=fcfa($potentiel)?></b></div><div class="stat">Annonces<b><?=count($ads)?></b></div><div class="stat">Propriétaires<b><?=count($us)?></b></div><div class="stat">Commission en vigueur<b><?=$taux?> %</b></div></div>
<div class="two">
<form method="post" class="fc" style="margin:0 0 16px;max-width:none"><h3>Commission</h3><?=field()?><input type="hidden" name="do" value="rate"><div class="acts"><input type="number" name="taux" step="0.5" min="0" max="100" value="<?=$taux?>" style="flex:1 1 90px"><button class="btn">Appliquer</button></div>
<div class="acts"><?php foreach([5,10,15,20] as $r):?><button class="btn g" name="taux" value="<?=$r?>"><?=$r?> %</button><?php endforeach?></div></form>
<form method="post" class="fc" style="margin:0 0 16px;max-width:none"><h3>Ajouter un quartier</h3><?=field()?><input type="hidden" name="do" value="quartier"><div class="acts"><select name="ville" style="flex:1 1 120px"><?php foreach($villes as $v)echo '<option value="'.$v['id'].'">'.e($v['nom']).'</option>'?></select><input name="nom" placeholder="Nom du quartier" required style="flex:2 1 150px"></div><div class="acts"><input name="lat" placeholder="Latitude (option)" style="flex:1 1 110px"><input name="lng" placeholder="Longitude (option)" style="flex:1 1 110px"><button class="btn o">Ajouter</button></div></form></div>
<form method="post" class="fc" style="max-width:none"><h3>Ajouter une ville</h3><?=field()?><input type="hidden" name="do" value="ville"><div class="acts"><input name="nom" placeholder="Ville" required style="flex:2 1 140px"><input name="lat" placeholder="Latitude" required style="flex:1 1 100px"><input name="lng" placeholder="Longitude" required style="flex:1 1 100px"><button class="btn o">Ajouter</button></div></form>
<div class="top"><h3>Toutes les annonces</h3><a class="btn o" href="publier.php">+ Publier</a></div>
<div class="tw"><table><tr><th>Annonce</th><th>Propriétaire</th><th>Prix · commission</th><th>Statut</th><th>Actions</th></tr>
<?php foreach($ads as $a):?><tr><td><a href="annonce.php?id=<?=$a['id']?>"><b><?=e($a['titre'])?></b></a><br><small><?=e($a['ville_nom'])?> › <?=e($a['quartier_nom'])?></small></td><td><?=e($a['proprio'])?><br><small><?=e($a['tel'])?></small></td>
<td><?=fcfa($a['prix'])?><br><small><?=$a['taux_commission_accepte']+0?> % = <?=fcfa($a['prix']*$a['taux_commission_accepte']/100)?></small></td><td><span class="st <?=$a['statut']?>"><?=t('st_'.$a['statut'])?></span></td>
<td><form method="post" class="a"><?=field()?><input type="hidden" name="do" value="ad"><input type="hidden" name="id" value="<?=$a['id']?>">
<?php if($a['statut']!=='conclue')echo '<button class="btn o" name="st" value="conclue">Louée / vendue</button>'; if($a['statut']!=='en_ligne')echo '<button class="btn" name="st" value="en_ligne">Remettre en ligne</button>'; if($a['statut']==='en_ligne')echo '<button class="btn g" name="st" value="retiree">Retirer</button>'?>
<a class="btn g" href="publier.php?id=<?=$a['id']?>"><?=t('edit')?></a><button class="btn r" name="st" value="del" onclick="return confirm('<?=$cf?>')">✕</button></form></td></tr><?php endforeach?></table></div>
<h3 style="margin:26px 0 10px">Propriétaires</h3>
<div class="tw"><table><tr><th>Nom</th><th>Téléphone</th><th>Orange Money / MoMo</th><th>Annonces</th><th></th></tr>
<?php foreach($us as $u):?><tr><td><?=e($u['nom'])?></td><td><?=e($u['telephone'])?></td><td>OM : <?=e($u['num_om']?:'—')?><br>MoMo : <?=e($u['num_momo']?:'—')?></td><td><?=$u['nb']?></td>
<td><form method="post" onsubmit="return confirm('Supprimer ce compte et toutes ses annonces ?')"><?=field()?><input type="hidden" name="do" value="user"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="btn r"><?=t('del')?></button></form></td></tr><?php endforeach?></table></div></div>
<?php foot();
