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
    m_cat.path AS menu_cat_path,
    m_cat.alias AS menu_cat_alias,
    m_cat.title AS menu_cat_title,
    m_cat.link AS menu_cat_link,
    m_cat.published AS menu_cat_published,
    m_article.path AS menu_article_path,
    m_article.alias AS menu_article_alias,
    m_article.title AS menu_article_title,
    m_article.link AS menu_article_link,
    m_article.published AS menu_article_published
FROM #__content AS c
LEFT JOIN #__categories AS cat ON c.catid = cat.id
LEFT JOIN #__menu AS m_cat ON m_cat.link LIKE CONCAT('%option=com_content&view=category&id=', CAST(c.catid AS CHAR))
LEFT JOIN #__menu AS m_article ON m_article.link LIKE CONCAT('%option=com_content&view=article&id=', CAST(c.id AS CHAR))
WHERE c.state = 1
ORDER BY c.created DESC;