select * from(SELECT row_number() over(order by students.STUDENTNO)as row,students.STUDENTNO xh,
students.NAME xm,
students.SEX xb,
CONVERT(varchar(10),students.ENTERDATE,20) as rxrq,
students.YEARS xz,
students.TAKEN tkzxf,
students.PASSED hdxf,
students.POINTS jdfh,
students.WARN txjg,
CONVERT(varchar(10),regdata.REGDATE,20) zcrq,
regcodeoptions.VALUE zczt,
statusoptions.VALUE xjzt,
classes.CLASSNAME bm,
schools.NAME xy
FROM STUDENTS LEFT OUTER JOIN REGDATA ON students.STUDENTNO = regdata.STUDENTNO
   AND  (regdata.REGCODE LIKE :REGCODE)
   AND  (regdata.YEAR = :YEAR)
   AND  (regdata.TERM = :TERM)
LEFT OUTER JOIN SCHOOLS ON students.SCHOOL = schools.SCHOOL
LEFT OUTER JOIN CLASSES ON STUDENTS.CLASSNO=CLASSES.CLASSNO AND (classes.CLASSNO LIKE :CLASSNO)
LEFT OUTER JOIN REGCODEOPTIONS ON regdata.REGCODE = regcodeoptions.NAME
LEFT OUTER JOIN STATUSOPTIONS ON students.STATUS = statusoptions.NAME
WHERE   (students.SCHOOL LIKE :SCHOOL)
   AND  (students.STATUS LIKE :STATUS)
)as b where b.row between :start and :end