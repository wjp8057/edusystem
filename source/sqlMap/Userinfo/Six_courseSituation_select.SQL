select * from(SELECT row_number() over(order by 修课方式,考核,学号) as row,学号 as studentno,姓名 as studentname,学生班级 as studentclass,
CAST(重修 AS INT) AS cx,
修课方式 as coursetype,考核 kh,
APPROACHCODE.NAME AS tkfs,
'' AS SL
FROM VIEWCOURSESELECTIONSTUDENTS
JOIN APPROACHCODE ON VIEWCOURSESELECTIONSTUDENTS.听课方式=APPROACHCODE.CODE
WHERE YEAR= :YEAR
AND TERM= :TERM
AND COURSENOGROUP =:COURSENO
) as b where b.row between :start and :end