SELECT
    c.id AS content_id,
    c.title AS content_title,
    c.alias AS content_alias,
    c.introtext,
    c.fulltext,
    c.created,
    c.modified,
    c.hits,
    c.metadesc,
    c.metakey,
    c.images,
    c.catid AS category_id,

    cat.title AS category_title,
    cat.alias AS category_alias,

    m.path AS menu_path,
    m.alias AS menu_alias,
    m.title AS menu_title,
    m.link AS menu_link

FROM #__content AS c
LEFT JOIN #__categories AS cat ON c.catid = cat.id
LEFT JOIN #__menu AS m ON m.link LIKE CONCAT('%id=', c.catid) 
WHERE c.state = 1 AND m.published = 1
ORDER BY c.created DESC;
