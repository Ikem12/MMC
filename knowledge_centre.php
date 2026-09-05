<?php
require_once __DIR__ . '/phase2.php';
p2_start(); $pdo = p2_db(); p2_check_csrf();
$categories = ['Authorities', 'Cases', 'Statutes', 'Practice notes', 'Templates', 'Phrase bank'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'publish';
    if ($action === 'bookmark' || $action === 'unbookmark') {
        $knowledgeId = (int)($_POST['knowledge_id'] ?? 0);
        if ($action === 'bookmark') $pdo->prepare('INSERT OR IGNORE INTO p3_knowledge_bookmarks (knowledge_id,user_id) VALUES (?,?)')->execute([$knowledgeId,p2_user_id()]);
        else $pdo->prepare('DELETE FROM p3_knowledge_bookmarks WHERE knowledge_id=? AND user_id=?')->execute([$knowledgeId,p2_user_id()]);
        p2_redirect('knowledge_centre.php?' . http_build_query(['category'=>$_POST['return_category'] ?? '', 'q'=>$_POST['return_q'] ?? '']), $action === 'bookmark' ? 'Item bookmarked.' : 'Bookmark removed.');
    }
    $title=trim($_POST['title'] ?? ''); $category=$_POST['category'] ?? 'Practice notes';
    if(!$title || !in_array($category,$categories,true)) p2_redirect('knowledge_centre.php','A title and valid library section are required.');
    $pdo->prepare('INSERT INTO p2_knowledge (title,category,summary,body,created_by) VALUES (?,?,?,?,?)')->execute([$title,$category,trim($_POST['summary']??''),trim($_POST['body']??''),p2_user_id()]);
    p2_log('knowledge.created',"Added knowledge item: $title"); p2_redirect('knowledge_centre.php?category='.rawurlencode($category),'Knowledge item published.');
}
$q=trim($_GET['q']??''); $category=$_GET['category']??''; $bookmarks=($_GET['bookmarks']??'')==='1';
if($category!==''&&!in_array($category,$categories,true))$category='';
if($q!=='') $pdo->prepare('INSERT INTO p3_searches (user_id,query) VALUES (?,?)')->execute([p2_user_id(),$q]);
$term='%'.$q.'%';
$stmt=$pdo->prepare("SELECT k.*, CASE WHEN b.knowledge_id IS NULL THEN 0 ELSE 1 END bookmarked FROM p2_knowledge k LEFT JOIN p3_knowledge_bookmarks b ON b.knowledge_id=k.id AND b.user_id=? WHERE (?='' OR k.category=?) AND (?=0 OR b.knowledge_id IS NOT NULL) AND (?='' OR k.title LIKE ? OR k.summary LIKE ? OR k.body LIKE ?) ORDER BY k.category,k.created_at DESC");
$stmt->execute([p2_user_id(),$category,$category,$bookmarks?1:0,$q,$term,$term,$term]); $items=$stmt->fetchAll();
$sections=array_fill_keys($categories,[]); foreach($items as $item)$sections[$item['category']][]=$item;
$stmt=$pdo->prepare('SELECT query, MAX(created_at) searched_at FROM p3_searches WHERE user_id=? GROUP BY query ORDER BY searched_at DESC LIMIT 5');$stmt->execute([p2_user_id()]);$recentSearches=$stmt->fetchAll();
p2_page('Knowledge Centre','knowledge_centre.php',function()use($categories,$category,$q,$bookmarks,$sections,$items,$recentSearches){ ?>
<div class="toolbar"><div><h1>Knowledge Centre</h1><p class="subhead">Authorities, cases, statutes and reusable drafting knowledge in one searchable library.</p></div><form class="inline" method="get"><input name="q" value="<?=p2_h($q)?>" placeholder="Search the knowledge library"><button>Search</button></form></div>
<nav class="category-tabs" aria-label="Knowledge sections"><a class="<?=!$category&&!$bookmarks?'active':''?>" href="knowledge_centre.php">All</a><?php foreach($categories as $itemCategory):?><a class="<?=$category===$itemCategory?'active':''?>" href="knowledge_centre.php?category=<?=rawurlencode($itemCategory)?>"><?=p2_h($itemCategory)?></a><?php endforeach?><a class="<?=$bookmarks?'active':''?>" href="knowledge_centre.php?bookmarks=1">Bookmarks</a></nav>
<div class="grid grid-2"><section class="card"><h2>Add to the library</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?=p2_csrf()?>"><div class="full"><label>Title *</label><input required name="title"></div><div><label>Section</label><select name="category"><?php foreach($categories as $itemCategory):?><option <?=$category===$itemCategory?'selected':''?>><?=p2_h($itemCategory)?></option><?php endforeach?></select></div><div><label>Summary</label><input name="summary"></div><div class="full"><label>Content</label><textarea name="body"></textarea></div><div><button>Publish</button></div></form></section><section class="card"><h2>Library overview</h2><div class="metric"><?=count($items)?></div><div class="metric-label"><?= $bookmarks ? 'Bookmarked items' : ($q!==''?'Matching items':'Published items')?></div><?php if($recentSearches):?><h3 class="section-heading">Recent searches</h3><ul class="compact-list"><?php foreach($recentSearches as $search):?><li><a href="knowledge_centre.php?q=<?=rawurlencode($search['query'])?>"><?=p2_h($search['query'])?></a><span><?=p2_h(date('d M',strtotime($search['searched_at'])))?></span></li><?php endforeach?></ul><?php else:?><p class="small">Searches are retained here for your next research session.</p><?php endif?></section></div>
<?php foreach($sections as $section=>$sectionItems):if(!$sectionItems)continue;?><section class="card knowledge-section"><div class="split"><h2><?=p2_h($section)?></h2><span class="badge"><?=count($sectionItems)?> item<?=count($sectionItems)===1?'':'s'?></span></div><?php foreach($sectionItems as $item):?><article class="knowledge-item"><div class="split"><strong><?=p2_h($item['title'])?></strong><form method="post"><input type="hidden" name="csrf" value="<?=p2_csrf()?>"><input type="hidden" name="action" value="<?=$item['bookmarked']?'unbookmark':'bookmark'?>"><input type="hidden" name="knowledge_id" value="<?=$item['id']?>"><input type="hidden" name="return_category" value="<?=p2_h($category)?>"><input type="hidden" name="return_q" value="<?=p2_h($q)?>"><button class="button secondary"><?=$item['bookmarked']?'Remove bookmark':'Bookmark'?></button></form></div><p><?=p2_h($item['summary'])?></p><?php if($item['body']):?><details><summary class="small">Read item</summary><p><?=nl2br(p2_h($item['body']))?></p></details><?php endif?></article><?php endforeach?></section><?php endforeach?>
<?php if(!$items):?><section class="card empty" style="margin-top:18px">No knowledge items match this view. Add an authority, case, statute or reusable practice resource.</section><?php endif?>
<?php }); ?>
