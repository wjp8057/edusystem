declare @year char(4)
declare @term char(2)
set @year=:YEAR
set @term=:TERM

update 教学质量评估详细
set used=0,Total = [1th] + [2th] + [3th] + [4th] + [5th] + [6th] + [7th] + [8th] + [9th] + [10th]
where exists (select * from 教学质量评估综合  where enabled=1 and  year=@year  and 教学质量评估综合.recno=教学质量评估详细.map)

--把无效的打分comelete设置为2
UPDATE 教学质量评估详细
SET total = 0, compelete = 2, used = 0
FROM 教学质量评估详细 INNER JOIN
      教学质量评估综合 ON 教学质量评估综合.recno = 教学质量评估详细.map
WHERE enabled=1 and year = @year and term=@term and (total=0 or total is null)

--去掉前10%，后90%。，其他的used位为1
DECLARE
@recno int
set @recno=0
declare tEvent CURSOR FAST_FORWARD
For select recno
from 教学质量评估综合
where year=@year and term=@term
open tEvent 
fetch tEvent into @recno
while @@fetch_status=0
begin
update 教学质量评估详细  
set used=1 
where recno in (select temptable1.recno from
(select top 90 percent recno 
from 教学质量评估详细 
where map=@recno and compelete=1 order by total desc)as temptable1 inner join 
(select top 90 percent recno from 教学质量评估详细 where map=@recno and compelete=1 order by total )
as temptable2 on temptable1.recno=temptable2.recno )
fetch tEvent into @recno
end 
close tEvent
deallocate tEvent

--计算平均分数
UPDATE 教学质量评估综合
SET score = temptable.average
FROM 教学质量评估综合 INNER JOIN
          (SELECT Map, cast(SUM(Total) / COUNT(*) as [decimal](8, 2) ) as average
         FROM 教学质量评估详细
         WHERE (Used = 1)
         GROUP BY Map) temptable ON temptable.Map = 教学质量评估综合.recno
WHERE (教学质量评估综合.[year] = @year) and 教学质量评估综合.term=@term