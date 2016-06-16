SELECT count(*)
FROM 教学质量评估院校评估 as temp inner join teachers on teachers.teacherno=temp.teacherno
WHERE TEMP.TEACHERNO LIKE :TEACHERNO AND teachers.SCHOOL=:SCHOOL AND TEMP.YEAR=:YEAR