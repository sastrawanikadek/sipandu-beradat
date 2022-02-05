package com.sipanduberadat.user.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.fragment.app.FragmentPagerAdapter
import com.sipanduberadat.user.fragments.BlockedRoadCoordinateFragment
import com.sipanduberadat.user.fragments.BlockedRoadInfoFragment

class BlockedRoadViewPagerAdapter(fragmentManager: FragmentManager):
        FragmentPagerAdapter(fragmentManager, BEHAVIOR_RESUME_ONLY_CURRENT_FRAGMENT) {
    private val fragments = listOf(
        BlockedRoadInfoFragment(),
        BlockedRoadCoordinateFragment()
    )

    override fun getCount(): Int {
        return fragments.size
    }

    override fun getItem(position: Int): Fragment {
        return fragments[position]
    }
}