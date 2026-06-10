<?php

declare(strict_types=1);

namespace App\Services\Residents;

use App\Models\EmergencyContact;
use App\Models\Resident;
use App\Repositories\Contracts\ResidentRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

/**
 * Encapsulates resident lifecycle: registration, family member attachment and
 * emergency contact management. All multi-step writes run inside transactions.
 */
class ResidentService extends BaseService
{
    public function __construct(ResidentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a primary resident record. Wraps in a transaction so a failed
     * audit write cannot leave a half-created row.
     */
    public function create(array $data): Resident
    {
        return DB::transaction(function () use ($data): Resident {
            /** @var Resident $resident */
            $resident = $this->repository->create($data);

            return $resident;
        });
    }

    /**
     * Update a resident and return the refreshed model.
     */
    public function update(int|string $id, array $data): Resident
    {
        return DB::transaction(function () use ($id, $data): Resident {
            /** @var Resident $resident */
            $resident = $this->repository->update($id, $data);

            return $resident;
        });
    }

    /**
     * Attach a family member to a primary resident. The new record inherits
     * the parent's `flat_id` and gets `type = family_member`.
     */
    public function attachFamilyMember(Resident $parent, array $data): Resident
    {
        return DB::transaction(function () use ($parent, $data): Resident {
            $data['parent_id'] = $parent->id;
            $data['flat_id']   = $data['flat_id'] ?? $parent->flat_id;
            $data['type']      = 'family_member';

            /** @var Resident $member */
            $member = $this->repository->create($data);

            return $member;
        });
    }

    /**
     * Add an emergency contact to a resident.
     */
    public function addEmergencyContact(Resident $resident, array $data): EmergencyContact
    {
        return DB::transaction(function () use ($resident, $data): EmergencyContact {
            return $resident->emergencyContacts()->create([
                'society_id' => $resident->society_id,
                ...$data,
            ]);
        });
    }
}
