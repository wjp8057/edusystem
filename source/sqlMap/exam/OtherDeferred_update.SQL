update scores
set examrem=:examrem
where year=:year
and term=:term
and studentno=:studentno
and courseno+[group]=:courseno