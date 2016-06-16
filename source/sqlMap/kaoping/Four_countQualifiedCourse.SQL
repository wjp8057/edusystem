select count(*)
from CourseQuality left outer join (select count(*) as amount,map from coursequalitydetail group by map ) as temp on temp.map=coursequality.recno
where year=:year and term=:term
