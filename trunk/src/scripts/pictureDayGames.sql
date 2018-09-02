--
-- Dump data for picture day
--
use schedule;
set @seasonName = "2018 - League";
set @divisions = '5U,6U,7U,8U,10U,12U,14U';
set @gameDate = "2018-09-15";

select
    date_format(d.day, "%c/%e/%Y") as Date,
    g.id as gameId,
    left(ifnull(t.actualStartTime, t.startTime), 5) as Start,
    concat(i.name, " ", i.gender) as division,
    a.name as facility,
    f.name as field,
    e.nameId as team,
    c.name as coach
from
    game as g
    join gameTime as t on
        t.id = g.gameTimeId
    join gameDate as d on
        d.id = t.gameDateId
        and d.day = @gameDate
    left outer join team as e on
        e.id = g.homeTeamId or e.id = g.visitingTeamId
    left outer join coach as c on
        c.teamId = e.id
    join flight as l on
        l.id = g.flightId
    join schedule as s on
        s.id = l.scheduleId
    join division as i on
        i.id = s.divisionId
        and find_in_set(i.name, @divisions)
    join field as f on
        f.id = t.fieldId
    join facility as a on
        a.id = f.facilityId
    join season as n on
        n.id = i.seasonId
        and n.name = @seasonName
order by
    d.id, left(ifnull(t.actualStartTime, t.startTime), 5), i.displayOrder
