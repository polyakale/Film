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
