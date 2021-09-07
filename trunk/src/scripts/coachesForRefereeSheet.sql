select
    concat(case when d.gender = 'Boys' then 'B' else 'G' end, d.name) as Division,
    right(t.nameId, (length(t.nameId) - position("-" in t.nameId))) as 'Team#',
    t.nameId as TeamID,
    c.name as Coach
from
    season as s
    join division as d on
        d.seasonId = s.id
        and d.scoringTracked = 1
        and d.name not in ("16U", "18U")
    join team as t on
        t.divisionId = d.id
    join coach as c on
        c.teamId = t.id
where
    s.name = '2021 - League'
order by 1, 2;