<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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

namespace Ampache\Module\Application\Album;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Model\ModelFactoryInterface;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UpdateFromTagsAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'update_from_tags';

    private ModelFactoryInterface $modelFactory;

    private UiInterface $ui;

    private ConfigContainerInterface $configContainer;

    public function __construct(
        ModelFactoryInterface $modelFactory,
        UiInterface $ui,
        ConfigContainerInterface $configContainer
    ) {
        $this->modelFactory    = $modelFactory;
        $this->ui              = $ui;
        $this->configContainer = $configContainer;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        require_once Ui::find_template('header.inc.php');
        
        $response = null;
        
        // Make sure they are a 'power' user at least
        if (!Access::check('interface', 75)) {
            Ui::access_denied();
            
            return $response;
        }
        $object_id  = (int) filter_input(INPUT_GET, 'album_id', FILTER_SANITIZE_NUMBER_INT);
        
        $album = $this->modelFactory->createAlbum($object_id);
        $album->format();
        
        $catalog_id = $album->get_catalogs();
        $type       = 'album';
        $target_url = sprintf(
            '%s/albums.php?action=show&amp;album=%d',
            $this->configContainer->getWebPath(),
            $object_id
        );
        
        require_once Ui::find_template('show_update_items.inc.php');
        
        $this->ui->showQueryStats();
        $this->ui->showFooter();
        
        return $response;
    }
}
