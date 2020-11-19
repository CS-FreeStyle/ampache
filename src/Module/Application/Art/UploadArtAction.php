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

namespace Ampache\Module\Application\Art;

use Ampache\Model\Art;
use Ampache\Model\ModelFactoryInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\System\Core;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UploadArtAction extends AbstractArtAction
{
    public const REQUEST_KEY = 'upload_art';

    private ModelFactoryInterface $modelFactory;

    private UiInterface $ui;

    public function __construct(
        ModelFactoryInterface $modelFactory,
        UiInterface $ui
    ) {
        $this->modelFactory = $modelFactory;
        $this->ui           = $ui;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        $burl = '';
        if (filter_has_var(INPUT_GET, 'burl')) {
            $burl = base64_decode(Core::get_get('burl'));
        }

        $object_type = filter_input(INPUT_GET, 'object_type', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $item        = $this->getItem();

        if ($item === null) {
            Ui::access_denied();

            return null;
        }

        $this->ui->showHeader();

        // we didn't find anything
        if (empty($_FILES['file']['tmp_name'])) {
            show_confirmation(
                T_('There Was a Problem'),
                T_('Art could not be located at this time. This may be due to write access error, or the file was not received correctly'),
                $burl
            );

            return null;
        }

        // Pull the image information
        $data       = ['file' => $_FILES['file']['tmp_name']];
        $image_data = Art::get_from_source($data, $object_type);

        // If we got something back insert it
        if ($image_data !== '') {
            $art = $this->modelFactory->createArt($item->id, $object_type);
            if ($art->insert($image_data, $_FILES['file']['type'])) {
                show_confirmation(T_('No Problem'), T_('Art has been added'), $burl);
            } else {
                show_confirmation(T_('There Was a Problem'), T_('Art file failed to insert, check the dimensions are correct.'), $burl);
            }
        }
        // Else it failed
        else {
            show_confirmation(
                T_('There Was a Problem'),
                T_('Art could not be located at this time. This may be due to write access error, or the file was not received correctly'),
                $burl
            );
        }

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}
