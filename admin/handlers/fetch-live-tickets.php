<?php
/**
 * Prime Beast - Live Ticket Stream Generator
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../core/db.php';

try {
    $tickets = $conn->query("SELECT * FROM support_tickets ORDER BY id DESC LIMIT 10");

    if($tickets && $tickets->num_rows > 0) {
        while($t = $tickets->fetch_assoc()) {
            // প্রায়োরিটি অনুযায়ী কালার লজিক
            $priorityClass = ($t['priority'] == 'High') 
                ? 'bg-rose-500/10 text-rose-500 border-rose-500/20' 
                : 'bg-blue-500/10 text-blue-500 border-blue-500/20';

            echo '
            <tr class="border-b border-white/5 hover:bg-white/5 transition group">
                <td class="p-6 pl-10">
                    <p class="text-white uppercase tracking-tighter">'.htmlspecialchars($t['subject']).'</p>
                    <p class="text-[9px] text-gray-500 font-bold uppercase mt-1">User ID: '.$t['user_id'].'</p>
                </td>
                <td class="p-6">
                    <span class="text-gray-400 uppercase text-[10px]">'.($t['category'] ?? 'GENERAL').'</span>
                </td>
                <td class="p-6 text-center">
                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase '.$priorityClass.' border">'.($t['priority'] ?? 'NORMAL').'</span>
                </td>
                <td class="p-6 text-right pr-10">
                    <button onclick="openTicket('.$t['id'].')" class="bg-blue-600/10 text-blue-500 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-xl transition text-[10px] uppercase font-black">Open Hub</button>
                </td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="4" class="text-center py-20 text-gray-500 uppercase text-[10px] tracking-widest">No active tickets found in stream.</td></tr>';
    }
} catch (Exception $e) {
    echo '<tr><td colspan="4" class="text-center py-20 text-rose-500 font-bold">STREAM_ERROR: '.$e->getMessage().'</td></tr>';
}