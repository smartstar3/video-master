<?php namespace MotionArray\Repositories;

use MotionArray\Models\ProjectInvitation;
use MotionArray\Models\User;

class ProjectInvitationRepository
{
    public function findByToken($token)
    {
        return ProjectInvitation::where('token', '=', $token)->first();
    }

    public function findByProject($projectId)
    {
        return ProjectInvitation::where('project_id', '=', $projectId)->get();
    }

    public function invitationEmailsByProject($projectId)
    {
        $projectInvitations = $this->findByProject($projectId);

        return $projectInvitations->pluck('email');
    }

    public function invitationEmailsByUser($userId)
    {
        $query = User::find($userId)->projects();

        $invitations = $query->join('project_invitations', 'project_invitations.project_id', '=', 'projects.id')
            ->select('project_invitations.*')
            ->get();

        return $invitations->pluck('email');
    }

    public function findReadInvitations(Array $emails = [])
    {
        $emails = array_unique(array_filter($emails));

        $query = ProjectInvitation::where('used', '=', 1);

        if (count($emails)) {
            $query->whereIn('email', $emails);
        }

        return $query->get();
    }

    public function create($email, $projectId)
    {
        $invitation = ProjectInvitation::create([
            'email' => $email,
            'project_id' => $projectId
        ]);

        return $invitation;
    }
}
