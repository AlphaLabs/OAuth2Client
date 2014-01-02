<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlphaLabs\OAuth2Client\Event;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use Doctrine\Common\Collections\ArrayCollection;

class CollectionDeserializationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_deserialize', 'method' => 'preDeserialize')
        );
    }

    public function preDeserialize(PreDeserializeEvent $event)
    {
//        if (is_array($event->getData())) {
//            $event->setData(new ArrayCollection($event->getData()));
//        }
    }
}
