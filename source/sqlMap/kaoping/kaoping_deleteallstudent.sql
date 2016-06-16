DELETE
FROM 教学质量评估详细
WHERE EXISTS
          (SELECT *
         FROM 教学质量评估综合
         WHERE 教学质量评估综合.recno = 教学质量评估详细.map AND year =:YEAR AND
               term = :TERM)