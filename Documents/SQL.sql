SELECT * FROM films fi;

SELECT * FROM favourites fa;



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

#get api/favourites/2/42 
select * from favourites
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


