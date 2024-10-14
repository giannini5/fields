-- drop database schedule;
-- create database schedule;
use schedule;

select
    g.id as game_id,
    abs(g.homeTeamScore - g.visitingTeamScore) as goal_differential,
    d.gender,
    d.name as division,
    s.name as season,
    year(gd.day) as year,
    case when abs(g.homeTeamScore - g.visitingTeamScore) >= 5 then 'Yes' else 'No' end as blow_out
from
    game as g
    join gameTime as gt on
        gt.gameId = g.id
    join gameDate as gd on
        gd.id = gt.gameDateId
    join schedule as s on
        s.id = g.scheduleId
    join division as d on
        d.id = s.divisionId
where
    g.homeTeamScore is not null
    and g.visitingTeamScore is not null;

