drop procedure if exists sp_addFacility;

delimiter $$

create procedure sp_addFacility(in _leagueName   varchar(60),
                                in _name         varchar(60),
                                in _address1     varchar(128),
                                in _address2     varchar(128),
                                in _city         varchar(128),
                                in _state        varchar(128),
                                in _postalCode   varchar(20),
                                in _country      varchar(128),
                                in _contactName  varchar(128),
                                in _contactEmail varchar(128),
                                in _contactPhone varchar(128),
                                in _image        varchar(128),
                                in _preApproved  tinyint,
                                in _enabled      tinyint)
BEGIN
    declare l_leagueId bigInt default NULL;

    select id into l_leagueId from league as l where l.name = _leagueName;

    if (l_leagueId is NULL) then
        set @errorMessage = concat("ABORT: league not found for : ", _leagueName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    insert into facility(
        leagueId,
        name,
        address1,
        address2,
        city,
        state,
        postalCode,
        country,
        contactName,
        contactEmail,
        contactPhone,
        image,
        preApproved,
        enabled)
    values (
        l_leagueId,
        _name,
        _address1,
        _address2,
        _city,
        _state,
        _postalCode,
        _country,
        _contactName,
        _contactEmail,
        _contactPhone,
        _image,
        _preApproved,
        _enabled)
    on duplicate key update
        address1     = _address1,
        address2     = _address2,
        city         = _city,
        state        = _state,
        postalCode   = _postalCode,
        country      = _country,
        contactName  = _contactName,
        contactEmail = _contactEmail,
        contactPhone = _contactPhone,
        image        = _image,
        preApproved  = _preApproved,
        enabled      = _enabled;
END $$

delimiter ;
