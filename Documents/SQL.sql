SELECT * FROM positions;
SELECT * FROM users;
SELECT * FROM favourites;

SELECT * FROM films;
SELECT * FROM people;
SELECT * FROM videos;
SELECT * FROM roles;
SELECT * FROM tasks;

SELECT fi.id, 
       fi.title, 
       fi.production, 
       fi.length, 
       fi.presentation, 
       fi.imdbLink, 
       ROUND(AVG(fa.evaluation),1) AS evaluation,
       GROUP_CONCAT(fa.userId) usersId
FROM films fi
LEFT JOIN favourites fa ON fi.id = fa.filmId
GROUP BY fi.id,
        fi.title, 
       fi.production, 
       fi.length, 
       fi.presentation, 
       fi.imdbLink
ORDER BY evaluation DESC;

# A kiválasztott film szerepői
SELECT f.title, p.name, r.role, f.id filmId, r.id roleId, p.id peopleId from tasks t
  JOIN people p ON t.personId = p.id
  JOIN films f  ON t.filmId = f.id
  JOIN roles r  ON t.roleId = r.id
where f.id = 2;

#Egy filmhez
SELECT p.name, f.title, r.role from tasks t
  JOIN people p ON t.personId = p.id
  JOIN films f  ON t.filmId = f.id
  JOIN roles r  ON t.roleId = r.id
  WHERE t.personId = 1082;
  

#get api/favourites/2/42 
select id, userId, filmId, evaluation from favourites
  WHERE userId = 6 AND filmId = 3
limit 1;

#patch api/favourites/2/42
update favourites set evaluation = 4.5
  WHERE userId = 6 AND filmId = 3;

#post api/favourites/2/42
INSERT INTO favourites 
(userId, filmId, evaluation) 
VALUES 
(6, 3, 3.5);

#delete api/favourites/2/42 
DELETE from favourites
  WHERE userId = 6 AND filmId = 3;

#Szereplők ABC
  #peopleAZ
  SELECT name, id FROM people
    order BY name;

#roles sort
#rolesAZ
SELECT role, id FROM roles
  ORDER BY role;


#filmPeopleRoles
SELECT roleId, personId, filmId FROM tasks;


  Select films.title, filmId, personId, roleId, count(*) db FROM tasks
    inner JOIN films on tasks.filmId = films.id
    group BY films.title, filmId, personId, roleId
    having db > 1;