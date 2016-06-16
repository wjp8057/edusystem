
SELECT row_number() over(
                         ORDER BY createdate) AS ROW,
       A.applydate_id,
       A.schoolview,
       A.deanview,
       convert(varchar(10), A.createdate,20) AS createdate,
       A.Studentno,
       B.NAME,
       A.projectname,
       A.credit,
       convert(varchar(10),A.certficatetime,20) AS certficatetime,
       A.firmno,
       A.submitter
FROM CreditSinglefirm AS A
INNER JOIN STUDENTS AS B ON A.Studentno=B.STUDENTNO
WHERE A.year=:year
  AND A.term=:term
  AND A.Studentno LIKE :studentno
  AND B.NAME LIKE :studentname
  AND B.CLASSNO LIKE :classno
  AND A.projectname LIKE :projectname
  AND (A.credittype = :cone
       OR A.credittype=:ctwo)
  AND A.schoolname LIKE :schoolname
  AND A.schoolview=1
  AND A.deanview=1