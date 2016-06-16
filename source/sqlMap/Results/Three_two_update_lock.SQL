update makeup
set lock=0
where year=:year and term=:term and courseno in (:courseno)