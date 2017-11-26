<?php

namespace FrontController\Module\Form;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FrontController\Core\ModuleInterface;
use RuntimeException;
use Swift_SmtpTransport;
use Swift_Message;
use Swift_Mailer;

class FormModule implements ModuleInterface
{
    public function handle(Application $app, Request $request, $template = null)
    {
        $html = '';
        $data = $request->request->all();
        $data[] = '---';
        $data['date/time'] = date('d/M/Y H:i');
        $data['ip'] = $request->getClientIp();
        $data['browser'] = $request->headers->get('User-Agent');
        $data['referer'] = $request->headers->get('Referer');

        foreach ($data as $key=>$value) {
            if ($key[0]!='_') {
                if ($value=='---') {
                    $html .= '<hr />';
                } else {
                    $html .= '<p><b>' . $key . '</b><br />';
                    $html .= '' . nl2br(htmlspecialchars($value)) . '</p>';
                }
            }
        }
        $html .= '<hr />';
        foreach ($data as $key=>$value) {
            if ($key[0]=='_') {
                $html .= '<p><b>' . substr($key, 1) . '</b><br />';
                $html .= '' . nl2br(htmlspecialchars($value)) . '</p>';
            }
        }

        $smtpServer = getenv('FRONTCONTROLLER_SMTP_SERVER');
        $smtpPort = getenv('FRONTCONTROLLER_SMTP_PORT');
        $smtpProtocol = getenv('FRONTCONTROLLER_SMTP_PROTOCOL');
        $smtpUsername = getenv('FRONTCONTROLLER_SMTP_USERNAME');
        $smtpPassword = getenv('FRONTCONTROLLER_SMTP_PASSWORD');

        if (!$smtpServer||!$smtpPort||!$smtpProtocol) {
            throw new RuntimeException("SMTP server configuration incomplete");
        }
        if (!$smtpUsername||!$smtpPassword) {
            throw new RuntimeException("SMTP credentials configuration incomplete");
        }

        $transport = (new Swift_SmtpTransport($smtpServer, $smtpPort, $smtpProtocol))
            ->setUsername($smtpUsername)
            ->setPassword($smtpPassword)
        ;

        $mailer = new Swift_Mailer($transport);

        $subject = 'New form';
        if ($request->attributes->has('subject')) {
            $subject = $request->attributes->get('subject');
        }

        foreach ($request->request->all() as $key=>$value) {
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }

        $recipient = $request->attributes->get('recipient');
        if ($request->attributes->has('recipient')) {
            $recipient = $request->attributes->get('recipient');
        }
        if (!$recipient) {
            throw new RuntimeException("No recipient specified");
        }

        // Create a message
        $message = (new Swift_Message($subject))
            ->setFrom([$recipient])
            ->setTo([$recipient])
            ->setBody($html, 'text/html')
        ;

        // Send the message
        $result = $mailer->send($message);

        //exit($html);

        $target = '/';
        if (isset($data['_target'])) {
            $target = $data['_target'];
        }
        if ($request->attributes->has('target')) {
            $target = $request->attributes->get('target');
        }
        if (!$target) {
            throw new RuntimeException("No target specified");
        }

        $response = new RedirectResponse(
            $target,
            302
        );

        return $response;
    }
}
