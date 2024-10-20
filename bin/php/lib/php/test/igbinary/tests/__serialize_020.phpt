--TEST--
issue when serializing/deserializing nested objects with __serialize
--SKIPIF--
<?php if (PHP_VERSION_ID < 70100) { echo "skip uses php 7.1 syntax\n"; } ?>
--FILE--
<?php
// Based on bug report seen in a Symfony codebase - https://github.com/igbinary/igbinary/issues/287
// NOTE: This test would also pass in older php versions, where igbinary doesn't call __serialize,
// just because we're returning the same data we fetch.
// (However, the serialized data generated before/after 7.4 would be incompatible if saved to memcache, etc.)
class Event
{
}

class MessageEvents
{
    private $events = [];
    private $transports = [];

    public function add(MessageEvent $event): void
    {
        $this->events[] = $event;
        $this->transports[$event->getTransport()] = true;
    }

    public function getEvents(?string $name = null): array
    {
        return $this->events;
    }
}

final class MessageEvent extends Event
{
    private $propagationStopped = false;
    private $message;
    private $envelope;
    private $transport;
    private $queued;

    public function __construct(RawMessage $message, Envelope $envelope, string $transport, bool $queued = false)
    {
        $this->message = $message;
        $this->envelope = $envelope;
        $this->transport = $transport;
        $this->queued = $queued;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    public function getMessage(): RawMessage
    {
        return $this->message;
    }
}

class Envelope
{
    protected $sender;
    protected $recipients = [];
    protected $senderSet = false;
    protected $recipientsSet = false;
    protected $message;

    public function __construct(Address $sender, array $recipients)
    {
        $this->setSender($sender);
        $this->setRecipients($recipients);
    }

    public static function create(RawMessage $message): self
    {
        return new DelayedEnvelope($message);
    }

    public function setSender(Address $sender): void
    {
        $this->sender = $sender;
    }

    public function setRecipients(array $recipients): void
    {
        $this->recipients = [];
        foreach ($recipients as $recipient) {
            $this->recipients[] = new Address($recipient->getAddress());
        }
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }
}

final class DelayedEnvelope extends Envelope
{

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function setSender(Address $sender): void
    {
        parent::setSender($sender);

        $this->senderSet = true;
    }

    public function setRecipients(array $recipients): void
    {
        parent::setRecipients($recipients);

        $this->recipientsSet = parent::getRecipients();
    }
}

final class Address
{
    private $address;
    private $name;

    public function __construct(string $address, string $name = '')
    {
        $this->address = trim($address);
        $this->name = trim(str_replace(["\n", "\r"], '', $name));
    }

    /**
     * @param Address|string $address
     */
    public static function create($address): self
    {
        if ($address instanceof self) {
            return $address;
        }
        if (\is_string($address)) {
            if (false === strpos($address, '<')) {
                return new self($address);
            }

            return new self($matches['addrSpec'], trim($matches['displayName'], ' \'"'));
        }

        throw new InvalidArgumentException(sprintf('An address can be an instance of Address or a string ("%s" given).', get_debug_type($address)));
    }

    public static function createArray(array $addresses): array
    {
        $addrs = [];
        foreach ($addresses as $address) {
            $addrs[] = self::create($address);
        }

        return $addrs;
    }
}

abstract class AbstractHeader
{
    private static $encoder;

    private $name;
    private $lineLength = 76;
    private $lang;
    private $charset = 'utf-8';
    protected $addresses = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

}

final class Headers
{
    private $headers = [];
    private $lineLength = 76;

    public function __construct(...$headers)
    {
        foreach ($headers as $header) {
            $this->add($header);
        }
    }

    public function __clone()
    {
        foreach ($this->headers as $name => $collection) {
            foreach ($collection as $i => $header) {
                $this->headers[$name][$i] = clone $header;
            }
        }
    }

    public function addMailboxListHeader(string $name, array $addresses): self
    {
        return $this->add(new MailboxListHeader($name, Address::createArray($addresses)));
    }

    public function add($header): self
    {
        self::checkHeaderClass($header);

        $name = strtolower($header->getName());

        $this->headers[$name][] = $header;

        return $this;
    }

    public function get(string $name)
    {
        $name = strtolower($name);
        if (!isset($this->headers[$name])) {
            return null;
        }

        $values = array_values($this->headers[$name]);

        return array_shift($values);
    }

    public function all(?string $name = null): iterable
    {
        if (null === $name) {
            foreach ($this->headers as $name => $collection) {
                foreach ($collection as $header) {
                    yield $name => $header;
                }
            }
        } elseif (isset($this->headers[strtolower($name)])) {
            foreach ($this->headers[strtolower($name)] as $header) {
                yield $header;
            }
        }
    }

    public static function checkHeaderClass($header): void
    {
        $name = strtolower($header->getName());
    }
}

final class MailboxListHeader extends AbstractHeader
{
    public function __construct(string $name, array $addresses)
    {
        parent::__construct($name);

        $this->addAddresses($addresses);
    }

    public function addAddresses(array $addresses)
    {
        foreach ($addresses as $address) {
            $this->addAddress($address);
        }
    }

    /**
     * @throws RfcComplianceException
     */
    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;
    }
}

class RawMessage
{
    protected $message;
    protected $headers;
    protected $body;
    protected $text;
    protected $textCharset;
    protected $html;
    protected $htmlCharset;
    protected $attachments = [];

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function __serialize(): array
    {
        return [$this->message];
    }

    public function __unserialize(array $data): void
    {
        [$this->message] = $data;
    }
}

class Message extends RawMessage
{

    public function __construct(?Headers $headers = null, ?AbstractPart $body = null)
    {
        $this->headers = $headers ? clone $headers : new Headers();
        $this->body = $body;
    }

    public function __clone()
    {
        $this->headers = clone $this->headers;

        if (null !== $this->body) {
            $this->body = clone $this->body;
        }
    }

    public function getHeaders(): Headers { return $this->headers; }

    public function __serialize(): array
    {
        return [$this->headers];
    }

    public function __unserialize(array $data): void
    {
        [$this->headers] = $data;
    }
}


class Email extends Message
{
    public function to(...$addresses)
    {
        return $this->setListAddressHeaderBody('To', $addresses);
    }

    private function setListAddressHeaderBody(string $name, array $addresses)
    {
        $addresses = Address::createArray($addresses);
        $headers = $this->getHeaders();
        $headers->addMailboxListHeader($name, $addresses);

        return $this;
    }

    /**
     * @internal
     */
    public function __serialize(): array
    {
        return [$this->text, $this->textCharset, $this->html, $this->htmlCharset, $this->attachments, parent::__serialize()];
    }

    /**
     * @internal
     */
    public function __unserialize(array $data): void
    {
        [$this->text, $this->textCharset, $this->html, $this->htmlCharset, $this->attachments, $parentData] = $data;

        parent::__unserialize($parentData);
    }
}

$messageEvents = new MessageEvents();
$messageEvents->add(new MessageEvent($message1 = (new Email())->to('alice@example.com'), Envelope::create($message1), 'null://null'));
$messageEvents->add(new MessageEvent($message2 = (new Email())->to('bob@example.com'), Envelope::create($message2), 'null://null'));

var_dump($messageEvents); // Comment/uncomment to trigger the bug

var_dump('headers_before', $messageEvents->getEvents()[0]->getMessage()->getHeaders() === $messageEvents->getEvents()[1]->getMessage()->getHeaders());

$ser = igbinary_serialize($messageEvents);

$messageEvents = igbinary_unserialize($ser);

// should dump "false", but dumps "true" the "var_dump($messageEvents)" is not commented
var_dump('headers_after', $messageEvents->getEvents()[0]->getMessage()->getHeaders() === $messageEvents->getEvents()[1]->getMessage()->getHeaders());
?>
--EXPECT--
object(MessageEvents)#1 (2) {
  ["events":"MessageEvents":private]=>
  array(2) {
    [0]=>
    object(MessageEvent)#2 (5) {
      ["propagationStopped":"MessageEvent":private]=>
      bool(false)
      ["message":"MessageEvent":private]=>
      object(Email)#3 (8) {
        ["message":protected]=>
        NULL
        ["headers":protected]=>
        object(Headers)#4 (2) {
          ["headers":"Headers":private]=>
          array(1) {
            ["to"]=>
            array(1) {
              [0]=>
              object(MailboxListHeader)#6 (5) {
                ["name":"AbstractHeader":private]=>
                string(2) "To"
                ["lineLength":"AbstractHeader":private]=>
                int(76)
                ["lang":"AbstractHeader":private]=>
                NULL
                ["charset":"AbstractHeader":private]=>
                string(5) "utf-8"
                ["addresses":protected]=>
                array(1) {
                  [0]=>
                  object(Address)#5 (2) {
                    ["address":"Address":private]=>
                    string(17) "alice@example.com"
                    ["name":"Address":private]=>
                    string(0) ""
                  }
                }
              }
            }
          }
          ["lineLength":"Headers":private]=>
          int(76)
        }
        ["body":protected]=>
        NULL
        ["text":protected]=>
        NULL
        ["textCharset":protected]=>
        NULL
        ["html":protected]=>
        NULL
        ["htmlCharset":protected]=>
        NULL
        ["attachments":protected]=>
        array(0) {
        }
      }
      ["envelope":"MessageEvent":private]=>
      object(DelayedEnvelope)#7 (5) {
        ["sender":protected]=>
        NULL
        ["recipients":protected]=>
        array(0) {
        }
        ["senderSet":protected]=>
        bool(false)
        ["recipientsSet":protected]=>
        bool(false)
        ["message":protected]=>
        object(Email)#3 (8) {
          ["message":protected]=>
          NULL
          ["headers":protected]=>
          object(Headers)#4 (2) {
            ["headers":"Headers":private]=>
            array(1) {
              ["to"]=>
              array(1) {
                [0]=>
                object(MailboxListHeader)#6 (5) {
                  ["name":"AbstractHeader":private]=>
                  string(2) "To"
                  ["lineLength":"AbstractHeader":private]=>
                  int(76)
                  ["lang":"AbstractHeader":private]=>
                  NULL
                  ["charset":"AbstractHeader":private]=>
                  string(5) "utf-8"
                  ["addresses":protected]=>
                  array(1) {
                    [0]=>
                    object(Address)#5 (2) {
                      ["address":"Address":private]=>
                      string(17) "alice@example.com"
                      ["name":"Address":private]=>
                      string(0) ""
                    }
                  }
                }
              }
            }
            ["lineLength":"Headers":private]=>
            int(76)
          }
          ["body":protected]=>
          NULL
          ["text":protected]=>
          NULL
          ["textCharset":protected]=>
          NULL
          ["html":protected]=>
          NULL
          ["htmlCharset":protected]=>
          NULL
          ["attachments":protected]=>
          array(0) {
          }
        }
      }
      ["transport":"MessageEvent":private]=>
      string(11) "null://null"
      ["queued":"MessageEvent":private]=>
      bool(false)
    }
    [1]=>
    object(MessageEvent)#8 (5) {
      ["propagationStopped":"MessageEvent":private]=>
      bool(false)
      ["message":"MessageEvent":private]=>
      object(Email)#9 (8) {
        ["message":protected]=>
        NULL
        ["headers":protected]=>
        object(Headers)#10 (2) {
          ["headers":"Headers":private]=>
          array(1) {
            ["to"]=>
            array(1) {
              [0]=>
              object(MailboxListHeader)#12 (5) {
                ["name":"AbstractHeader":private]=>
                string(2) "To"
                ["lineLength":"AbstractHeader":private]=>
                int(76)
                ["lang":"AbstractHeader":private]=>
                NULL
                ["charset":"AbstractHeader":private]=>
                string(5) "utf-8"
                ["addresses":protected]=>
                array(1) {
                  [0]=>
                  object(Address)#11 (2) {
                    ["address":"Address":private]=>
                    string(15) "bob@example.com"
                    ["name":"Address":private]=>
                    string(0) ""
                  }
                }
              }
            }
          }
          ["lineLength":"Headers":private]=>
          int(76)
        }
        ["body":protected]=>
        NULL
        ["text":protected]=>
        NULL
        ["textCharset":protected]=>
        NULL
        ["html":protected]=>
        NULL
        ["htmlCharset":protected]=>
        NULL
        ["attachments":protected]=>
        array(0) {
        }
      }
      ["envelope":"MessageEvent":private]=>
      object(DelayedEnvelope)#13 (5) {
        ["sender":protected]=>
        NULL
        ["recipients":protected]=>
        array(0) {
        }
        ["senderSet":protected]=>
        bool(false)
        ["recipientsSet":protected]=>
        bool(false)
        ["message":protected]=>
        object(Email)#9 (8) {
          ["message":protected]=>
          NULL
          ["headers":protected]=>
          object(Headers)#10 (2) {
            ["headers":"Headers":private]=>
            array(1) {
              ["to"]=>
              array(1) {
                [0]=>
                object(MailboxListHeader)#12 (5) {
                  ["name":"AbstractHeader":private]=>
                  string(2) "To"
                  ["lineLength":"AbstractHeader":private]=>
                  int(76)
                  ["lang":"AbstractHeader":private]=>
                  NULL
                  ["charset":"AbstractHeader":private]=>
                  string(5) "utf-8"
                  ["addresses":protected]=>
                  array(1) {
                    [0]=>
                    object(Address)#11 (2) {
                      ["address":"Address":private]=>
                      string(15) "bob@example.com"
                      ["name":"Address":private]=>
                      string(0) ""
                    }
                  }
                }
              }
            }
            ["lineLength":"Headers":private]=>
            int(76)
          }
          ["body":protected]=>
          NULL
          ["text":protected]=>
          NULL
          ["textCharset":protected]=>
          NULL
          ["html":protected]=>
          NULL
          ["htmlCharset":protected]=>
          NULL
          ["attachments":protected]=>
          array(0) {
          }
        }
      }
      ["transport":"MessageEvent":private]=>
      string(11) "null://null"
      ["queued":"MessageEvent":private]=>
      bool(false)
    }
  }
  ["transports":"MessageEvents":private]=>
  array(1) {
    ["null://null"]=>
    bool(true)
  }
}
string(14) "headers_before"
bool(false)
string(13) "headers_after"
bool(false)
