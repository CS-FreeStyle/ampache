<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Playlist;

use Ampache\Model\Playlist;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CreatePlaylistAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'create_playlist';

    private UiInterface $ui;

    public function __construct(
        UiInterface $ui
    ) {
        $this->ui = $ui;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        $this->ui->showHeader();

        /* Check rights */
        if (!Access::check('interface', 25)) {
            Ui::access_denied();

            $this->ui->showQueryStats();
            $this->ui->showFooter();

            return null;
        }

        $playlist_name = (string) scrub_in($_REQUEST['playlist_name']);
        $playlist_type = (string) scrub_in(filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS));

        $playlist_id                     = Playlist::create($playlist_name, $playlist_type);
        $_SESSION['data']['playlist_id'] = $playlist_id;
        show_confirmation(
            T_('Playlist created'),
            /* HINT: %1 playlist name, %2 playlist type */
            sprintf(T_('%1$s (%2$s) has been created'), $playlist_name, $playlist_type),
            'playlist.php'
        );

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}
