insert into 教学质量评估综合(teacherno,courseno,task,year,term)
select teacherno,courseno,task,year,term from
(select distinct TEACHERPLAN.teacherno,scheduleplan.courseno+scheduleplan.[group] AS COURSENO,CASE [task]
 WHEN 'L' THEN 'K'
 WHEN 'N' THEN 'U'
 ELSE 'S'
 END AS TASK, teacherplan.year AS YEAR, teacherplan.term AS  TERM
 from teacherplan inner join scheduleplan on teacherplan.map=scheduleplan.recno INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO
 where teacherplan.year=:YEAR and teacherplan.term=:TERM ) as temp
where not exists  (select * from 教学质量评估综合 where 教学质量评估综合.year=:YEAR AND 教学质量评估综合.term=:TERM AND 教学质量评估综合.TEACHERNO=temp.teacherno
and temp.courseno=教学质量评估综合.courseno
and 教学质量评估综合.task=temp.task);