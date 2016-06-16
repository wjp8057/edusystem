SELECT 年 AS Y, 月份 as M, 学院 as SCHOOL, 学号 as SNO, 姓名 as N, 成绩 as R, 等级 as L
FROM 计算机统考成绩
WHERE 学号 = :STUDENTNO
ORDER BY SNO,Y, M