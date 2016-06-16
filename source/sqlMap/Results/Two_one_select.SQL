select * from (SELECT row_number() over(ORDER BY selections.课号)as row,selections.课号 as kh,selections.课名 as km,selections.学分 as xf,selections.人数 as rs
FROM (SELECT 课号, 课名,学分,学年, 学期, COUNT(*) AS 人数
        FROM l_cj_view_selectnopass15
        GROUP BY 课号, 课名,学分, 学年, 学期) AS selections
WHERE selections.人数 > :RENSHU
AND selections.学年 = :YEAR
AND selections.学期 = :TERM
) as b where b.row between :start and :end