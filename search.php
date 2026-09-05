<?php
require_once __DIR__ . '/phase2.php';
p2_start(); $pdo=p2_db(); $q=trim($_GET['q']??''); $results=[];
if($q!==''){
  $term='%'.$q.'%';
  $queries=[
    ["SELECT 'Matter' type,id,COALESCE(reference,title) label,COALESCE(practice_area,'') detail,'matters.php?id=' || id url FROM p2_matters WHERE reference LIKE ? OR title LIKE ? OR description LIKE ?",[$term,$term,$term]],
    ["SELECT 'Client' type,id,name label,COALESCE(email,phone,'') detail,'client_view.php?id=' || id url FROM p2_clients WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?",[$term,$term,$term]],
    ["SELECT 'Document' type,id,title label,COALESCE(content,document_type,'') detail,'matters.php?id=' || matter_id || '&tab=documents' url FROM p2_documents WHERE title LIKE ? OR content LIKE ?",[$term,$term]],
    ["SELECT 'Knowledge' type,id,title label,COALESCE(summary,body,'') detail,'knowledge_centre.php?q=' || ? url FROM p2_knowledge WHERE title LIKE ? OR summary LIKE ? OR body LIKE ?",[$q,$term,$term,$term]],
    ["SELECT 'Task' type,t.id,t.title label,COALESCE(m.reference || ' · ','') || t.status detail,'matters.php?id=' || t.matter_id || '&tab=tasks' url FROM p3_tasks t LEFT JOIN p2_matters m ON m.id=t.matter_id WHERE t.title LIKE ? OR t.description LIKE ?",[$term,$term]],
    ["SELECT 'Research' type,r.id,r.title label,COALESCE(r.research_type,'Research') detail,'matters.php?id=' || r.matter_id || '&tab=research' url FROM p3_research r WHERE r.title LIKE ? OR r.summary LIKE ?",[$term,$term]],
    ["SELECT 'Counsel action' type,a.id,a.title label,COALESCE(a.result,a.status) detail,'counsel_engine.php?matter_id=' || a.matter_id url FROM p3_counsel_actions a WHERE a.title LIKE ? OR a.result LIKE ?",[$term,$term]],
    ["SELECT 'Practice area' type,MIN(id) id,practice_area label,COUNT(*) || ' matters' detail,'matters.php' url FROM p2_matters WHERE practice_area LIKE ? AND practice_area != '' GROUP BY practice_area",[$term]],
  ];
  foreach($queries as [$sql,$params]){$stmt=$pdo->prepare($sql);$stmt->execute($params);$results=array_merge($results,$stmt->fetchAll());}
  $pdo->prepare('INSERT INTO p3_searches (user_id,query) VALUES (?,?)')->execute([p2_user_id(),$q]);
}
p2_page('Universal search','search.php',function()use($q,$results){?><div class="toolbar"><div><h1>Universal search</h1><p class="subhead">Search clients, matters, tasks, documents, knowledge and practice areas.</p></div></div><section class="card"><form method="get" class="inline"><div style="flex:1;min-width:240px"><label>Search all workspaces</label><input autofocus name="q" value="<?=p2_h($q)?>" placeholder="Enter a reference, name, title, task or keyword"></div><button>Search</button></form></section><?php if($q!==''):?><section style="margin-top:18px"><p class="small"><?=count($results)?> result(s) for “<?=p2_h($q)?>”</p><?php if(!$results):?><div class="card empty">No matching records.</div><?php else:?><div class="grid"><?php foreach($results as $item):?><a class="card search-result" href="<?=p2_h($item['url'])?>"><div class="split"><strong><?=p2_h($item['label'])?></strong><span class="badge"><?=p2_h($item['type'])?></span></div><div class="small"><?=p2_h(strlen((string)$item['detail'])>180?substr((string)$item['detail'],0,177).'...':(string)$item['detail'])?></div></a><?php endforeach?></div><?php endif?></section><?php endif?><?php }); ?>
