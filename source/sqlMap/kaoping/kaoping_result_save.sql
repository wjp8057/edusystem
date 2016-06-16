declare @year char(4)
declare @term char(2)
set @year=:YEAR
set @term=:TERM

--删除原有信息
delete from  教学质量评估成绩
where  学年=@year and 学期=@term

--成绩归档
INSERT INTO 教学质量评估成绩
     (学年, 学期, 教师号, 教师, 课号, 课名, 有效人数, 态度, 内容, 方法, 效果, 类型, 得分, 学院,参加人数,完成人数,开课)
SELECT DISTINCT 
      TEMP .YEAR AS 学年, TEMP .TERM AS 学期, teachers.TEACHERNO AS 教师号, 
      RTRIM(TEMP .NAME) AS 教师, TEMP .COURSENO AS 课号, 
      RTRIM(COURSES.COURSENAME) AS 课名, TEMP .AMOUNT AS 有效人数, 
      TEMP .one AS 态度, TEMP .two AS 内容, TEMP .three AS 方法, TEMP .four 效果, 
      教学质量评估类型.NAME AS 类型, 
      TEMP .one + TEMP .two + TEMP .three + TEMP .four AS 得分, schools.name AS 学院, 
      temp2.amount AS 参加人数, temp3.amount AS 完成人数,cschool.name as 开课
FROM (SELECT 教学质量评估综合.TEACHERNO, NAME, 教学质量评估综合.RECNO, TASK, 
              COURSENO, YEAR, TERM, SCORE, COUNT(*) AS AMOUNT, 
              cast(SUM([1th] + [2th]) / (COUNT(*) * 1.00) AS [decimal](8, 2)) AS one, 
              cast(SUM([3th] + [4th]) / (COUNT(*) * 1.00) AS [decimal](8, 2)) AS two, 
              cast(SUM([5th] + [6th] + [7th] + [8th]) / (COUNT(*) * 1.00) AS [decimal](8, 2)) 
              AS three, cast(SUM([9th] + [10th]) / (COUNT(*) * 1.00) AS [decimal](8, 2)) 
              AS four
        FROM 教学质量评估综合 INNER JOIN
              TEACHERS ON 
              TEACHERS.TEACHERNO = 教学质量评估综合.TEACHERNO LEFT OUTER JOIN
              教学质量评估详细 ON 
              教学质量评估详细.MAP = 教学质量评估综合.RECNO
        WHERE 教学质量评估详细.used = 1 AND 教学质量评估综合.enabled = 1
        GROUP BY 教学质量评估综合.TEACHERNO, NAME, 教学质量评估综合.RECNO, TASK, 
              COURSENO, YEAR, TERM, SCORE) AS TEMP left outer JOIN
      教学质量评估类型 ON 教学质量评估类型.TYPE = TEMP .TASK INNER JOIN
      COURSES ON COURSES.COURSENO = SUBSTRING(TEMP .COURSENO, 1, 7) 
      inner join schools as cschool on cschool.school=courses.school
      LEFT OUTER JOIN
      COURSEPLAN ON COURSEPLAN.YEAR = TEMP .YEAR AND 
      COURSEPLAN.TERM = TEMP .TERM AND 
      COURSEPLAN.COURSENO + COURSEPLAN.[GROUP] = TEMP .COURSENO LEFT OUTER
       JOIN
      CLASSES ON CLASSES.CLASSNO = COURSEPLAN.CLASSNO LEFT OUTER JOIN
      teachers ON teachers.teacherno = TEMP .teacherno LEFT OUTER JOIN
      schools ON teachers.school = schools.school left outer JOIN
          (SELECT COUNT(*) AS AMOUNT, 教学质量评估详细.map recno
         FROM 教学质量评估详细
         GROUP BY map) AS temp2 ON temp2.recno = TEMP .recno left outer JOIN
          (SELECT COUNT(*) AS amount, map recno
         FROM 教学质量评估详细
         WHERE Compelete = 1
         GROUP BY map) AS temp3 ON temp3.recno = TEMP .recno
WHERE TEMP.YEAR=@year and Temp.term=@term
ORDER BY 学年, 学期, 教师号, 课号