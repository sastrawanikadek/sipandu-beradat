package com.sipanduberadat.guest.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.fragment.app.FragmentPagerAdapter
import com.sipanduberadat.guest.fragments.FamilyFragment
import com.sipanduberadat.guest.fragments.RequestFamilyFragment

class MainFamilyViewPagerAdapter(fragmentManager: FragmentManager):
        FragmentPagerAdapter(fragmentManager, BEHAVIOR_RESUME_ONLY_CURRENT_FRAGMENT) {
    val fragments = listOf(
            FamilyFragment(),
            RequestFamilyFragment()
    )

    override fun getCount(): Int {
        return fragments.size
    }

    override fun getItem(position: Int): Fragment {
        return fragments[position]
    }

    override fun getPageTitle(position: Int): CharSequence {
        return if (position == 0) "Family" else "Family Request"
    }
}