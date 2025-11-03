<?php

namespace App\Http\Controllers;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Http\Request;

class MsGraphController extends Controller
{
    /**
     * Connect to Microsoft Graph
     */
    public function connect()
    {
        return MsGraph::connect();
    }
    
    /**
     * Handle OAuth callback
     */
    public function oauth()
    {
        // The package automatically handles the OAuth callback
        // Just return the connect method which will handle the callback
        return MsGraph::connect();
    }
    
    /**
     * Disconnect from Microsoft Graph
     */
    public function disconnect()
    {
        MsGraph::disconnect();
        
        return redirect()->route('calendar.index')
            ->with('success', 'Disconnected from Microsoft 365.');
    }
}