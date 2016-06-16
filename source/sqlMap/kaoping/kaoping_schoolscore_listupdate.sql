insert into 教学质量评估院校评估(YEAR,TEACHERNO)
select distinct year,teacherno from teacherplan
where  exists (select * from scheduleplan where scheduleplan.recno=teacherplan.map)
and year=:YEAR and not exists (select * from 教学质量评估院校评估
where 教学质量评估院校评估.year=teacherplan.year and 教学质量评估院校评估.teacherno=teacherplan.teacherno);

UPDATE 教学质量评估院校评估
SET SSCORE=temp.total
FROM  教学质量评估院校评估 INNER JOIN (SELECT 教师号 as teacherno,学年 as year,avg(得分) as total FROM  教学质量评估成绩
GROUP BY 教师号,学年) as temp on temp.year=教学质量评估院校评估.year
and temp.teacherno=教学质量评估院校评估.teacherno
WHERE 教学质量评估院校评估.YEAR=:YEAR