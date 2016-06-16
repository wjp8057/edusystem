SELECT row_number() over(
                         ORDER BY c.studentno) AS ROW,
       recno,
       c.status,
       c.studentno,
       s.name,
       c.year,
       c.term,
       convert(varchar(10),c.certficatetime,20) AS certficatetime,
       projectname,
       c.credit,
       convert(varchar(10),addtime,20) AS addtime
FROM creditcount c
INNER JOIN students s ON s.studentno=c.studentno
INNER JOIN schools ON schools.school = s.SCHOOL
WHERE projectname LIKE :projectname
  AND subschool LIKE :subschool
  AND credittype=:ctype
  AND c.status LIKE :status
  AND c.studentno LIKE :studentno
  AND schools.school LIKE :schoolno