<?php
/** Filesystem-only regression runner: php tests/LegacyMigrationArchiveTest.php */
require dirname(__DIR__) . '/services/LegacyMigrationArchive.php';
use humhub\modules\orgmap\services\LegacyMigrationArchive;
$checks=0;$root=sys_get_temp_dir().'/orgmap-archive-check-'.bin2hex(random_bytes(6));mkdir($root,0700);
function verify($value,$message){global $checks;if(!$value)throw new RuntimeException($message);$checks++;}
function fixture($overlay=true){global $root;static $i=0;$path=$root.'/'.++$i;mkdir($path.'/migrations/legacy',0700,true);$source=dirname(__DIR__).'/migrations/legacy';foreach(glob($source.'/*.php') as $file){copy($file,$path.'/migrations/legacy/'.basename($file));if($overlay)copy($file,$path.'/migrations/'.basename($file));}copy($source.'/manifest.json',$path.'/migrations/legacy/manifest.json');return $path;}
function rejected($path){try{LegacyMigrationArchive::prepare($path);verify(false,'Unsafe layout accepted');}catch(LogicException $e){verify(true,'Rejected');}}
$path=fixture();$manifest=json_decode(file_get_contents($path.'/migrations/legacy/manifest.json'),true);file_put_contents($path.'/migrations/m260101_000000_custom.php','<?php // Local extension retained');LegacyMigrationArchive::prepare($path);verify(count(glob($path.'/migrations/legacy/overlaid/*',GLOB_ONLYDIR))===1,'No unique batch');
foreach($manifest as $name=>$hash){verify(!file_exists($path.'/migrations/'.$name),'Historical file still executable');verify(hash_file('sha256',$path.'/migrations/legacy/'.$name)===$hash,'Canonical archive changed');$copies=glob($path.'/migrations/legacy/overlaid/*/'.$name);verify(count($copies)===1&&hash_file('sha256',$copies[0])===$hash,'Original leftover not retained byte-identically');}
verify(is_file($path.'/migrations/m260101_000000_custom.php'),'Custom migration removed');LegacyMigrationArchive::prepare($path);verify(count(glob($path.'/migrations/legacy/overlaid/*',GLOB_ONLYDIR))===1,'Repeat created unnecessary batch');
copy(dirname(__DIR__).'/migrations/legacy/uninstall.php',$path.'/migrations/uninstall.php');LegacyMigrationArchive::prepare($path);verify(count(glob($path.'/migrations/legacy/overlaid/*/uninstall.php'))===2,'Repeated upload overwrote a backup');
$clean=fixture(false);LegacyMigrationArchive::prepare($clean);verify(!file_exists($clean.'/migrations/legacy/.archive.lock'),'Clean install requires writes');
$bad=fixture();file_put_contents($bad.'/migrations/uninstall.php','<?php // Local modification');rejected($bad);verify(count(glob($bad.'/migrations/*.php'))===35,'Mismatch moved part of batch');verify(!file_exists($bad.'/migrations/legacy/overlaid'),'Mismatch created backup batch');
$bad=fixture();unlink($bad.'/migrations/uninstall.php');symlink($bad.'/migrations/legacy/uninstall.php',$bad.'/migrations/uninstall.php');rejected($bad);verify(is_link($bad.'/migrations/uninstall.php'),'Source symlink altered');
$bad=fixture();unlink($bad.'/migrations/legacy/uninstall.php');symlink($bad.'/migrations/uninstall.php',$bad.'/migrations/legacy/uninstall.php');rejected($bad);
$bad=fixture();symlink($root.'/outside',$bad.'/migrations/legacy/.archive.lock');rejected($bad);verify(!file_exists($root.'/outside'),'Followed symlink lock');
$bad=fixture();file_put_contents($bad.'/migrations/legacy/overlaid','blocked');rejected($bad);verify(count(glob($bad.'/migrations/*.php'))===35,'Unsafe destination moved originals');
$bad=fixture();chmod($bad.'/migrations/legacy',0500);try{rejected($bad);verify(count(glob($bad.'/migrations/*.php'))===35,'Read-only failure lost files');}finally{chmod($bad.'/migrations/legacy',0700);}
$bad=fixture();$data=$manifest;$data['../uninstall.php']=$data['uninstall.php'];unset($data['uninstall.php']);file_put_contents($bad.'/migrations/legacy/manifest.json',json_encode($data));rejected($bad);
$bad=fixture();unlink($bad.'/migrations/legacy/uninstall.php');rejected($bad);
$partial=fixture();mkdir($partial.'/migrations/legacy/overlaid',0700);mkdir($partial.'/migrations/legacy/overlaid/interrupted',0700);rename($partial.'/migrations/uninstall.php',$partial.'/migrations/legacy/overlaid/interrupted/uninstall.php');LegacyMigrationArchive::prepare($partial);verify(count(glob($partial.'/migrations/legacy/overlaid/*/*.php'))===35,'Partial retry lost or duplicated originals');verify(count(glob($partial.'/migrations/*.php'))===0,'Partial retry left executable historical files');
echo json_encode(['checks'=>$checks,'result'=>'PASS','fixtures'=>$root],JSON_PRETTY_PRINT).PHP_EOL;
