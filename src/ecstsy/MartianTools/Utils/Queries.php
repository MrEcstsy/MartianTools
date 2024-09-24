<?php

namespace ecstsy\MartianTools\Utils;

class Queries {

    // PLAYER QUERY
    public const PLAYERS_INIT   = "players.initialize";
    public const PLAYERS_SELECT = "players.select";
    public const PLAYERS_CREATE = "players.create";
    public const PLAYERS_UPDATE = "players.update";
    public const PLAYERS_DELETE = "players.delete";

    // WARNINGS QUERY
    public const WARNINGS_INIT       = "warnings.initialize";
    public const WARNINGS_SELECT     = "warnings.select";
    public const WARNINGS_CREATE     = "warnings.create";
    public const WARNINGS_UPDATE = "warnings.update";  
    public const WARNINGS_DELETE     = "warnings.delete";

    // MUTES QUERY
    public const MUTES_INIT      = "mutes.initialize";
    public const MUTES_SELECT    = "mutes.select";
    public const MUTES_CREATE    = "mutes.create";
    public const MUTES_UPDATE     = "mutes.update";
    public const MUTES_SELECT_ALL = "mutes.select_by_player";

    // REPORTS QUERY
    public const REPORTS_INIT      = "reports.initialize";
    public const REPORTS_SELECT    = "reports.select";
    public const REPORTS_CREATE    = "reports.create";
    public const REPORTS_UPDATE    = "reports.update";
    public const REPORTS_SELECT_ALL = "reports.select_by_player";

    // KICKS QUERY
    public const KICKS_INIT        = "kicks.initialize";
    public const KICKS_CREATE      = "kicks.create";
    public const KICKS_UPDATE     = "kicks.update";
    public const KICKS_SELECT_ALL  = "kicks.select_by_player";

}