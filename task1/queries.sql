#выборка списка постов с фильтром по категории и постраничной разбивкой
SELECT
  p.id,
  p.cat_id,
  p.title,
  p.likes,
  c. NAME AS cat
FROM
  posts p
  INNER JOIN categories c ON p.cat_id = c.id
WHERE
  p.id > 1 #Используем p.id вместо OFFSET для оптимизации постраничной разбивки
LIMIT 50;

#Выбираем конкретный пост
SELECT
  p.id,
  p.cat_id,
  p.title,
  p.likes,
  c. NAME AS cat
FROM
  posts p
  INNER JOIN categories c ON p.cat_id = c.id
WHERE
  p.id = 1;

#изменение контента поста
UPDATE posts
SET cat_id = 1,
  title = 'New title',
  content = 'New content'
WHERE
  id = 1;


#ставим блокировку на строку и увеличиваем счетчик лайков
BEGIN;

SELECT
  likes
FROM
  posts
WHERE
  id = 1 FOR UPDATE;

UPDATE posts
SET likes = likes + 1
WHERE
  id = 1;

COMMIT;

SELECT
  user_id,
  display_name,
  img_folder
FROM
  likes
WHERE
  post_id = 1;

#при изменении данных профиля пользовтеля изменяем данные в записях с лайками
UPDATE likes
SET display_name = 'New name',
  img_folder = 'New picture path'
WHERE
  user_id = 1;