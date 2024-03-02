<?php
/* ----------------------------------------------------------------------------

Open Report Analyzer
Copyright (C) 2023 - John Bradley (userjack6880)

includes/template-loader.php
  glue bit to load templates - may be a bit superfulous

Available at: https://github.com/userjack6880/Open-Report-Analyzer

-------------------------------------------------------------------------------

This file is part of Open Report Analyzer.

Open Report Analyzer is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY 
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
this program.  If not, see <https://www.gnu.org/licenses/>.

---------------------------------------------------------------------------- */

// real simple, get the name of the running script, and include the equivalent template file

include_once ('templates/'.TEMPLATE.'/index.php');

// If there's more things later I need to load templates, I'll add them here.

?>
