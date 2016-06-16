select b.*,c.classname,c.classno,p.value from bookpayment b
left join students s on s.studentno = b.no 
left join classes c on c.classno=s.classno 
left join statusoptions p on p.name=s.status 
where b.apply_id = :applyId and s.name like :name and b.no like :studentno and b.type='0' order by b.no