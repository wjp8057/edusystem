select * from(SELECT row_number() over(ORDER BY 修课方式,考核,学号) as row,
学号 xh,姓名 xm,学生班级 xsbj,
CAST(重修 AS INT) AS cx,
修课方式 as xkfs,考核 as kh,
APPROACHCODE.NAME AS tkfs,
'' AS SL
FROM VIEWCOURSESELECTIONSTUDENTS
JOIN APPROACHCODE ON VIEWCOURSESELECTIONSTUDENTS.听课方式=APPROACHCODE.CODE
WHERE YEAR= :YEAR
AND TERM= :TERM
AND COURSENOGROUP =:COURSENO
) as b where b.row between :start and :end