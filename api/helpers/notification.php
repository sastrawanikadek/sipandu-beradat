<?php
    use Kreait\Firebase\Messaging\CloudMessage;
    use Kreait\Firebase\Exception\Messaging\NotFound;
    use Kreait\Firebase\Exception\Messaging\InvalidMessage;
    
    function send_notifications($factory, array $tokens, array $data) {
        $messaging = $factory->createMessaging();
        
        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token ? $token : "")
                    ->withData($data)
                    ->withHighestPossiblePriority();
                $messaging->send($message);
            } catch(NotFound $e) {
                $mysqli = connect_db();
                $query = "DELETE FROM token_notifikasi_masyarakat WHERE token = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $stmt->close();
                
                $query = "DELETE FROM token_notifikasi_petugas WHERE token = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $stmt->close();
                
                $query = "DELETE FROM token_notifikasi_tamu WHERE token = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $stmt->close();
            } catch(InvalidMessage $e) {
                continue;
            }
        }
    }