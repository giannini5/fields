use schedule;
select * from game limit 10;

select
    pgs.goals,
    gd.day,
    p.name,
    t.nameId,
    t.name as team_name,
    d.name as division,
    d.gender as gender,
    s.name as season
from
    playerGameStats as pgs
    join game as g on
        g.id = pgs.gameId
    join gameDate as gd on
        gd.id = g.gameDateId
    join player as p on
        p.id = pgs.playerId
    join team as t on
        t.id = p.teamId
    join division as d on
        d.id = t.divisionId
    join season as s on
        s.id = d.seasonId
        and s.name like '2021%';
