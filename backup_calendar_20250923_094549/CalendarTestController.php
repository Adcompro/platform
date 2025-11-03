<?php

namespace App\Http\Controllers;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Http\Request;

class CalendarTestController extends Controller
{
    /**
     * Test Microsoft Graph connection and permissions
     */
    public function test()
    {
        if (!MsGraph::isConnected()) {
            return response()->json([
                'connected' => false,
                'message' => 'Not connected to Microsoft 365'
            ]);
        }

        $results = [];

        // Test 1: Get user profile
        try {
            $user = MsGraph::get('me');
            $results['user_profile'] = [
                'success' => true,
                'data' => [
                    'name' => $user['displayName'] ?? 'N/A',
                    'email' => $user['mail'] ?? $user['userPrincipalName'] ?? 'N/A',
                    'id' => $user['id'] ?? 'N/A'
                ]
            ];
        } catch (\Exception $e) {
            $results['user_profile'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        // Test 2: Check calendar access
        try {
            $calendars = MsGraph::get('me/calendars');
            $results['calendars'] = [
                'success' => true,
                'count' => count($calendars['value'] ?? []),
                'calendars' => array_map(function($cal) {
                    return [
                        'name' => $cal['name'] ?? 'N/A',
                        'id' => $cal['id'] ?? 'N/A'
                    ];
                }, $calendars['value'] ?? [])
            ];
        } catch (\Exception $e) {
            $results['calendars'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        // Test 3: Try to get events
        try {
            $events = MsGraph::get('me/events', [
                '$top' => 5,
                '$orderby' => 'start/dateTime desc'
            ]);
            $results['events'] = [
                'success' => true,
                'count' => count($events['value'] ?? [])
            ];
        } catch (\Exception $e) {
            $results['events'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        // Test 4: Check mailbox settings
        try {
            $mailboxSettings = MsGraph::get('me/mailboxSettings');
            $results['mailbox_settings'] = [
                'success' => true,
                'timezone' => $mailboxSettings['timeZone'] ?? 'N/A'
            ];
        } catch (\Exception $e) {
            $results['mailbox_settings'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        return response()->json([
            'connected' => true,
            'results' => $results,
            'recommendations' => $this->getRecommendations($results)
        ]);
    }

    private function getRecommendations($results)
    {
        $recommendations = [];

        if (!$results['user_profile']['success']) {
            $recommendations[] = 'Cannot access user profile. Check if User.Read permission is granted.';
        }

        if (!$results['calendars']['success']) {
            if (strpos($results['calendars']['error'] ?? '', 'MailboxNotEnabledForRESTAPI') !== false) {
                $recommendations[] = 'This account appears to be on-premises Exchange or has REST API disabled. Contact your IT administrator to enable Exchange Online or REST API access.';
            } else {
                $recommendations[] = 'Cannot access calendars. Check if Calendars.Read permission is granted.';
            }
        }

        if (!$results['events']['success']) {
            $recommendations[] = 'Cannot access calendar events. This might be a permission issue or mailbox configuration problem.';
        }

        return $recommendations;
    }
}