select * from(select row_number() over(order by recno)as row,coursename as kcmc,teacher as rkls,recno,isnull(amount,0) as rs,'' as SL,'' as SA
from CourseQuality left outer join (select count(*) as amount,map from coursequalitydetail group by map ) as temp on temp.map=coursequality.recno
where year=:year and term=:term
)as b where b.row between :start and :end
