SELECT temp.teacherno as jsh,teachers.name as xm,
cast(temp.sscore as numeric(5,2)) as xspf,cast(temp.tscore as numeric(5,0)) as thpf,cast(temp.xscore as numeric(5,0)) as xypf,
cast(temp.addscore as numeric(5,0)) as qtpf,temp.rem as bz
,0 as total
FROM 教学质量评估院校评估 as temp inner join teachers on teachers.teacherno=temp.teacherno
WHERE TEMP.TEACHERNO LIKE :TEACHERNO AND teachers.SCHOOL=:SCHOOL AND TEMP.YEAR=:YEAR