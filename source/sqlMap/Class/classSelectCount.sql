select Count(Classes.ClassNo) As ROWS
from Classes
JOIN SCHOOLS ON CLASSES.SCHOOL=SCHOOLS.SCHOOL
where CLASSES.ClassNo like :CLASSNO
and CLASSES.ClassName like :CLASSNAME
and CLASSES.School like :SCHOOL
and CAST(YEAR(Classes.YEAR) AS CHAR) LIKE :YEAR
