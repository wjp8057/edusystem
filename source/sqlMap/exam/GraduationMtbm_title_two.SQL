SELECT classes.classname
FROM STUDENTS, classes
WHERE students.classno = classes.classno AND studentno = :STUDENTNO;