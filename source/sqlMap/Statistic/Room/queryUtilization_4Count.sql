select c.roomno 
	from timesectors t,classrooms c left outer join [sxj_计算教室利用率] l on l.roomno = c.roomno,roomoptions r,areas a,roomstatus s 
	where l.time=t.name and year=:year and term=:term and c.seats >=:seats_gt and  c.seats<=:seats_lt and day<6 
				and (l.time<>'A' and l.time<>'B' and l.time<>'I' and l.time<>'L') and r.name=c.equipment and c.status=s.code 
				and c.area=a.name and jsn like :jsn and area like :area 
				and c.roomno like :roomno  and c.equipment like :equipment 
				and s.code like :code 
	group by c.roomno,c.jsn,c.seats,a.value, c.building, r.value, s.name