select * from (select ROW_NUMBER ()  over(order by recno) as row, recno,one,two,three,four,five,six,seven,eight,nine,ten,total
from CourseQualityDetail
where map=:recno)as b where b.row between :start and :end