import React, {FC, useEffect} from 'react';
import styled from 'styled-components';
import {
  Dropdown,
  IconButton,
  MoreVerticalIcon,
  SubNavigationItem,
  SubNavigationPanel,
  useBooleanState
} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';

type SubNavigationType = {
  title?: string;
  sections: SubNavigationSection[];
  entries: SubNavigationEntry[];
  backLink?: BackLink;
  stateCode?: string;
};

type SubNavigationEntry = {
  code: string;
  route: string;
  routeParams?: {[key: string]: any};
  title: string;
  sectionCode: string;
};

type SubNavigationSection = {
  code: string;
  title: string;
};

type BackLink = {
  title: string;
  route: string;
};

type Props = SubNavigationType & {
  activeSubEntryCode: string | null;
};

const SubNavigation: FC<Props> = ({title, sections, entries, backLink, stateCode, activeSubEntryCode}) => {
  const translate = useTranslate();
  const router = useRouter();
  const [isMenuOpen, openMenu, closeMenu] = useBooleanState(false);
  const subNavigationState = sessionStorage.getItem(`collapsedColumn_${stateCode}`);
  const [isSubNavigationOpened, openSubNavigation, closeSubNavigation] = useBooleanState(subNavigationState === undefined || subNavigationState === '1');

  useEffect(() => {
    sessionStorage.setItem(`collapsedColumn_${stateCode}`, isSubNavigationOpened ? '1' : '0');
  }, [isSubNavigationOpened]);

  const handleFollowSubEntry = (event: any, subEntry: SubNavigationEntry) => {
    event.stopPropagation();
    event.preventDefault();
    closeMenu();
    router.redirect(router.generate(subEntry.route, subEntry.routeParams));
  };

  return (
    <SubNavContainer>
      <SubNavigationPanel isOpen={isSubNavigationOpened} open={openSubNavigation} close={closeSubNavigation}>
        {!isSubNavigationOpened &&
        <Dropdown>
            <IconButton
                level="tertiary"
                title=''
                icon={<MoreVerticalIcon />}
                ghost="borderless"
                onClick={openMenu}
                className="dropdown-button"
            />
          {isMenuOpen &&
          <Dropdown.Overlay onClose={closeMenu}>
              <Dropdown.Header>
                {title && <Dropdown.Title>{translate(title)}</Dropdown.Title>}
              </Dropdown.Header>
              <Dropdown.ItemCollection>
                {entries.map(subEntry =>
                  <Dropdown.Item onClick={(event: any) => handleFollowSubEntry(event, subEntry)} key={subEntry.code}>
                    {subEntry.title}
                  </Dropdown.Item>
                )}
              </Dropdown.ItemCollection>
          </Dropdown.Overlay>
          }
        </Dropdown>
        }
        {isSubNavigationOpened &&
        <>
          {backLink &&
          // @ts-ignore
          <Backlink onClick={() => router.redirectToRoute(backLink.route)}>
            {translate(backLink.title)}
          </Backlink>
          }
          {sections.map(section => {
            return (
              <Section key={section.code}>
                <SectionTitle>{translate(section.title)}</SectionTitle>
                {entries.filter(subNav => subNav.sectionCode === section.code).map(subEntry => {
                  return (
                    <SubNavigationItem
                      active={subEntry.code === activeSubEntryCode}
                      key={subEntry.code}
                      href={`#${router.generate(subEntry.route, subEntry.routeParams)}`}
                      onClick={(event: any) => handleFollowSubEntry(event, subEntry)}
                    >
                      {subEntry.title}
                    </SubNavigationItem>
                  );
                })}
              </Section>
            );
          })}
        </>
        }
      </SubNavigationPanel>
    </SubNavContainer>
  );
}

const SubNavContainer = styled.div``;

const SectionTitle = styled.div`
  margin-bottom: 20px;
  color: ${({theme}) => theme.color.grey100};
  text-transform: uppercase;
  font-size: 11px;
  line-height: 20px;
`;

const Section = styled.div`
  :not(:first-child) {
    margin-top: 30px;
  }
`;

const Backlink = styled.div`
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.grey140};
  cursor: pointer;
  padding-bottom: 10px;
`;

export {SubNavigation, SubNavigationSection, SubNavigationEntry};
export type {SubNavigationType};