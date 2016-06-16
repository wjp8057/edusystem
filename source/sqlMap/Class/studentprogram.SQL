SELECT 编号 as bianhao,名称 as name,CONVERT(varchar(10),制定日期,20) as time,备注 as rem,计划类别 programtype,学院 as school
FROM VIEWR28
WHERE STUDENTNO=:STUDENTNO
